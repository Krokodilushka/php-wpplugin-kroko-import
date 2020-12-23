<?php

namespace KrokoImport\Model;

use KrokoImport\Exceptions\AttachmentException;
use KrokoImport\Exceptions\Exception;
use KrokoImport\Exceptions\PostAlreadyExistsException;
use KrokoImport\Exceptions\WpCommentNotFoundException;
use KrokoImport\Exceptions\WpPostNotFoundException;
use WP_Post;

require_once ABSPATH . '/wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class ImportPosts
{

    const WP_POST_META_KEY_ID = 'kroko_import_post_id';
    const WP_CATEGORY_META_KEY_ID = 'kroko_import_category_id';
    const WP_COMMENT_META_KEY_ID = 'kroko_import_comment_id';
    const WP_POST_THUMBNAIL_URL_META_KEY_ID = 'kroko_import_thumbnail_url';

    private $_log = array();

    function __construct()
    {
        if ($role = get_role('administrator')) $role->add_cap('unfiltered_upload');
    }

    /*
     * logs
     */

    function getLogs()
    {
        return $this->_log;
    }

    function clearLogs()
    {
        return $this->_log = array();
    }

    private function writeLogPost($postID, $message)
    {
        $this->_log['posts'][$postID][] = $message;
    }

    /*
     * public
     */

    /**
     *
     * @param \KrokoImport\Data\FeedOptions $feedOptions
     */
    function perform($feedOptions)
    {
        $xml = XMLParser::load($feedOptions->getUrl());
        $parsed = XMLParser::parse($xml);
        if (!empty($parsed->countPosts()) > 0) {
            foreach ($parsed->getPosts() as $post) {
                $this->writeLogPost($post->getID(), 'начало обработки XML поста');
                try {
                    $this->insertOrUpdateWPPost($post, $feedOptions->getOnExistsUpdate());
                    $this->insertOrUpdateWPComment($post, NULL, $post->getComments());
                    $this->insertOrUpdateWPThumbnail($post, $post->getThumbnail());
                } catch (PostAlreadyExistsException $e) {
                    $this->writeLogPost($post->getID(), 'такой пост уже найден. по настройкам фида не нужно обновлять');
                }
                $this->writeLogPost($post->getID(), 'конец обработки XML поста');
//                break;
            }
        } else {
            $this->writeLogPost($post->getID(), 'в XML нет постов');
        }
    }

    /*
     * private
     */

    /**
     *
     * @param \KrokoImport\Data\XML\Post $xmlPost
     * @param boolean $updateIfExists
     */
    private function insertOrUpdateWPPost($xmlPost, $updateIfExists)
    {
        // про вставке эти параметы нужны будут точно
        $wpInsertPostArgs = array(
            'comment_status' => 'open', // 'closed' означает, что комментарии закрыты.
            'ping_status' => 'closed', // 'closed' означает, что пинги и уведомления выключены.
            'post_author' => 1, // id автора
            'post_status' => 'publish', // Статус создаваемой записи.
            'post_type' => 'post', // Тип записи.
            'post_title' => $xmlPost->getTitle(), // Заголовок (название) записи.
            'post_content' => $xmlPost->getContent(), // Полный текст записи.
        );


        // поиск поста с таким id
        try {
            $wpPost = $this->getWPPostByXMLPost($xmlPost);
            // будет изменние поста
            if (!$updateIfExists) {
                throw new PostAlreadyExistsException();
            }
            $this->writeLogPost($xmlPost->getID(), 'будет обновление поста');
            $wpInsertPostArgs['ID'] = $wpPost->ID;
            $wpInsertPostArgs['post_date'] = $wpPost->post_date;
        } catch (WpPostNotFoundException $e) {
            // будет вставка нового поста, нужно установить дату
            $this->writeLogPost($xmlPost->getID(), 'будет вставка поста');
            $wpInsertPostArgs['post_date'] = $xmlPost->getDate()->format('Y-m-d H:i:s');
        }

        // есть slug?
        if ($xmlPost->getSlug() !== NULL) {
            $this->writeLogPost($xmlPost->getID(), 'есть slug ' . $xmlPost->getSlug());
            $wpInsertPostArgs['post_name'] = $xmlPost->getSlug(); // Альтернативное название записи (slug) будет использовано в УРЛе.
        }

        // сбор мета в массив
        $metas = array(
            self::WP_POST_META_KEY_ID => $xmlPost->getID() // уникальный id этого поста точно нужен в meta
        );
        if ($xmlPost->getMetas()->count() > 0) {
            $this->writeLogPost($xmlPost->getID(), 'есть мета: ' . $xmlPost->getMetas()->count());
            foreach ($xmlPost->getMetas()->get() as $meta) {
                // если в xml мета использовался ключ, который используется для идентификации поста, то кинуть исключение
                if ($meta->getKey() == self::WP_POST_META_KEY_ID) {
                    throw new \Exception('Illegal meta ' . self::WP_POST_META_KEY_ID . '');
                }
                $metas[$meta->getKey()] = $meta->getValue();
            }
        }
        // сбор тегов в массив
        $tags = array();
        if ($xmlPost->getTags()->count() > 0) {
            $this->writeLogPost($xmlPost->getID(), 'есть теги: ' . $xmlPost->getTags()->count());
            foreach ($xmlPost->getTags()->get() as $tag) {
                $tags[] = $tag;
            }
        }
        // вставка, изменение и сбор категорий в массив
        $categories = array();
        if ($xmlPost->getCategories()->count() > 0) {
            $this->writeLogPost($xmlPost->getID(), 'есть категории: ' . $xmlPost->getCategories()->count());
            foreach ($xmlPost->getCategories()->get() as $category) {
                $categories[] = $this->insertOrUpdateCategory($xmlPost->getID(), $category);
            }
        }
        if (!empty($categories)) {
            $wpInsertPostArgs['post_category'] = $categories; // Категории к которой относится пост.
        }
        $wpInsertPostArgs['tags_input'] = $tags; // Метки поста (указываем ярлыки, имена или ID).
        $wpInsertPostArgs['meta_input'] = $metas; // добавит указанные мета поля. По умолчанию: ''. с версии 4.4.
        $postID = wp_insert_post(wp_slash($wpInsertPostArgs));
        if (!is_numeric($postID)) {
            throw new Exception('ошибка при получении id только что вставленного поста');
        }
        $this->writeLogPost($xmlPost->getID(), 'ID поста в WP : ' . $postID);
        return $postID;
    }

    /**
     *
     * @param \KrokoImport\Data\XML\KeyValue $category
     */
    private function insertOrUpdateCategory($postID, $category)
    {
        $this->writeLogPost($postID, 'категория key: ' . $category->getKey() . ', value: ' . $category->getValue());
        // параметры категории для вставки в wp
        $insertCatArgs = array(
            'cat_name' => $category->getValue(),
        );
        // попытаться получить категорию с таким id(из XML)
        $wpCatsByXMLID = get_categories(array(
            'meta_key' => self::WP_CATEGORY_META_KEY_ID,
            'meta_value' => $category->getKey(),
        ));
        // если категория была найдена...
        if (!empty($wpCatsByXMLID)) {
            // изменить категорию
            $insertCatArgs['cat_ID'] = current($wpCatsByXMLID)->ID;
        }
        // вставить или изменить категорию
        $insertID = wp_insert_category($insertCatArgs, true);
        if (isset($insertID->error_data['term_exists'])) {
            $this->writeLogPost($postID, 'категория обновилась');
            $insertID = $insertID->error_data['term_exists'];
        } else if (is_numeric($insertID)) {
            // при апдейте было бы и так совпадение, при вставке нужно добавить к категории наш id из xml
            $this->writeLogPost($postID, 'категория вставилась');
            add_term_meta($insertID, self::WP_CATEGORY_META_KEY_ID, $category->getKey(), true);
        } else {
            throw new Exception('wp_insert_category response error');
        }
        return $insertID;
    }

    /**
     * @param \KrokoImport\Data\XML\Post $xmlPost
     * @param int $replyTo wp comment id
     * @param \KrokoImport\Data\XML\Comments $comments
     */
    private function insertOrUpdateWPComment($xmlPost, $replyTo, $comments)
    {
        $wpPost = $this->getWPPostByXMLPost($xmlPost);
        // добавление комментариев
        if ($comments->count() > 0) {
            foreach ($comments->get() as $comment) {
                $this->writeLogPost($xmlPost->getID(), 'комментарий с xml ID ' . $comment->getID() . ', replyTo: ' . $replyTo);
                $insertCommentArgs = array(
                    'comment_post_ID' => $wpPost->ID,
                    'comment_author' => $comment->getAuthor(),
                    'comment_author_email' => md5($comment->getAuthor()) . '@kroko-import',
                    'comment_content' => $comment->getText(),
                    'comment_parent' => $replyTo ?: 0,
                    'comment_meta' => array()
                );
                if ($comment->getMeta()->count() > 0) {
                    $this->writeLogPost($xmlPost->getID(), 'у комментария wp есть мета ' . $comment->getMeta()->count());
                    foreach ($comment->getMeta()->get() as $meta) {
                        if ($meta->getKey() == self::WP_COMMENT_META_KEY_ID) {
                            throw new \Exception('illegal meta ' . self::WP_COMMENT_META_KEY_ID . '');
                        }
                        $insertCommentArgs['comment_meta'][$meta->getKey()] = $meta->getValue();
                    }
                }
                // поиск комментария
                try {
                    $currentComment = $this->getWPCommentByXMLComment($comment);
                    // есть такой комментарии, обновить
                    $this->writeLogPost($xmlPost->getID(), 'обновление');
                    $commentID = $currentComment->comment_ID;
                    $insertCommentArgs['comment_ID'] = $commentID;
                    wp_update_comment($insertCommentArgs);
                } catch (WpCommentNotFoundException $e) {
                    // вставить новый
                    $this->writeLogPost($xmlPost->getID(), 'вставка');
                    $insertCommentArgs['comment_date'] = $comment->getDate()->format('Y-m-d H:i:s');
                    $insertCommentArgs['comment_meta'][self::WP_COMMENT_META_KEY_ID] = $comment->getID();
                    $insertCommentArgs['comment_approved'] = 1;
                    $commentID = wp_insert_comment($insertCommentArgs);
                }
                $replies = $comment->getReplies();
                if (isset($commentID) && is_numeric($commentID) && $commentID > 0 && $replies !== NULL && $replies->count() > 0) {
                    $this->writeLogPost($xmlPost->getID(), 'есть вложенные комментарии к xml ID ' . $comment->getID() . ', count: ' . $replies->count());
                    $this->insertOrUpdateWPComment($xmlPost, $commentID, $replies);
                }
            }
        }
    }

    /**
     *
     * @param \KrokoImport\Data\XML\Post $xmlPost
     * @param string $thumbnailUrl
     * @throws Exception
     */
    private function insertOrUpdateWPThumbnail($xmlPost, $thumbnailUrl)
    {
        $this->writeLogPost($xmlPost->getID(), 'thumbnail ' . $thumbnailUrl);
        // поиск поста с таким id
        $wpPost = $this->getWPPostByXMLPost($xmlPost);
        $thumbnailMeta = get_post_meta($wpPost->ID, self::WP_POST_THUMBNAIL_URL_META_KEY_ID, true);
        if (!$thumbnailUrl) {
            $this->writeLogPost($xmlPost->getID(), 'thumbnail delete');
            delete_post_thumbnail($wpPost->ID);
        } else if (!$thumbnailMeta || $thumbnailMeta != $thumbnailUrl) {
            $this->writeLogPost($xmlPost->getID(), 'thumbnail insert/update');
            $attachmentID = media_sideload_image($thumbnailUrl, $wpPost->ID, $wpPost->title, 'id');
            if ($attachmentID instanceof \WP_Error) {
                throw new AttachmentException('ошибка при загрузке thumbnail. wperror: ' . $attachmentID->get_error_code() . ' ' . $attachmentID->get_error_message() . ' ' . $attachmentID->get_error_data());
            }
            if (!is_numeric($attachmentID)) {
                throw new AttachmentException('ошибка при загрузке thumbnail. $attachmentID: ' . $attachmentID);
            }
            if (!set_post_thumbnail($wpPost->ID, $attachmentID)) {
                throw new AttachmentException('ошибка set_post_thumbnail');
            }
            update_post_meta($wpPost->ID, self::WP_POST_THUMBNAIL_URL_META_KEY_ID, $thumbnailUrl);
        } else {
            $this->writeLogPost($xmlPost->getID(), 'thumbnail не обновлен. в meta уже есть этот url?: ' . ($thumbnailUrl == $thumbnailMeta));
        }
    }

    /**
     *
     * @param \KrokoImport\Data\XML\Post $post
     * @return WP_Post
     * @throws WpPostNotFoundException
     */
    private function getWPPostByXMLPost($post)
    {
        $wpPost = get_posts(array(
            'meta_key' => self::WP_POST_META_KEY_ID,
            'meta_value' => $post->getID(),
            'post_type' => 'post',
        ));
        if (!empty($wpPost)) {
            return current($wpPost);
        } else {
            throw new WpPostNotFoundException('wp post by xml post ID ' . $post->getID() . ' not found');
        }
    }

    /**
     *
     * @param \KrokoImport\Data\XML\Comment $xmlComment
     * @return \WP_Comment
     * @throws WpCommentNotFoundException
     */
    private function getWPCommentByXMLComment($xmlComment)
    {
        $wpComments = get_comments(array(
            'meta_key' => self::WP_COMMENT_META_KEY_ID,
            'meta_value' => $xmlComment->getID(),
        ));
        if (!empty($wpComments)) {
            return current($wpComments);
        } else {
            throw new WpCommentNotFoundException('wp comment by xml comment ID ' . $xmlComment->getID() . ' not found');
        }
    }

}
