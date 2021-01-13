<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Data\XML\Post;
use KrokoImport\Exceptions\Attachment_Exception;
use KrokoImport\Exceptions\Exception;
use KrokoImport\Exceptions\Wp_Category_Not_Found_Exception;
use KrokoImport\Exceptions\Wp_Post_Not_Found_Exception;
use WP_Post;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import_Post extends Loggable
{

    const WP_POST_META_KEY_ID = 'kroko_import_post_id';
    const WP_POST_THUMBNAIL_URL_META_KEY_ID = 'kroko_import_thumbnail_url';
    const DEFAULT_POST_ARGS = [
        'comment_status' => 'open', // 'closed' означает, что комментарии закрыты.
        'ping_status' => 'closed', // 'closed' означает, что пинги и уведомления выключены.
        'post_author' => 1, // id автора
        'post_status' => 'publish', // Статус создаваемой записи.
        'post_type' => 'post', // Тип записи.
    ];

    private $_importCategory;

    function __construct(array &$_logs, Import_Category $import_category)
    {
        parent::__construct($_logs);
        $this->_import_category = $import_category;
    }

    public function getWPPostByXMLPostId(string $xmlPostId): WP_Post
    {
        $wpPost = get_posts(array(
            'meta_key' => self::WP_POST_META_KEY_ID,
            'meta_value' => $xmlPostId,
            'post_type' => 'post',
        ));
        if (!empty($wpPost)) {
            return current($wpPost);
        } else {
            throw new Wp_Post_Not_Found_Exception('Wp post by xml post ID ' . $xmlPostId . ' not found');
        }
    }

    public function insert(Post $xml_post): int
    {
        $args = self::DEFAULT_POST_ARGS;
        $args['post_title'] = $xml_post->getTitle();
        $args['post_content'] = $xml_post->getContent();
        $args['post_date'] = $xml_post->getDate()->format('Y-m-d H:i:s');
        return $this->insertDb($args, $xml_post);
    }

    public function update(WP_Post $wpPost, Post $xmlPost): int
    {
        $args = self::DEFAULT_POST_ARGS;
        $args['post_title'] = $xmlPost->getTitle();
        $args['post_content'] = $xmlPost->getContent();
        $args['ID'] = $wpPost->ID;
        $args['post_date'] = $wpPost->post_date;
        return $this->insertDb($args, $xmlPost);
    }

    public function getThumbnailUrl(int $postId): ?string
    {
        return get_post_meta($postId, self::WP_POST_THUMBNAIL_URL_META_KEY_ID, true);
    }

    public function setThumbnailUrl(string $postId, string $url): void
    {
        update_post_meta($postId, self::WP_POST_THUMBNAIL_URL_META_KEY_ID, $url);
    }

    public function deletePostThumbnail(string $postId): void
    {
        delete_post_thumbnail($postId);
        delete_post_meta($postId, self::WP_POST_THUMBNAIL_URL_META_KEY_ID);
    }

    public function setPostThumbnail(string $postId, string $url, ?string $description)
    {
        $attachmentID = media_sideload_image($url, $postId, $description, 'id');
        if ($attachmentID instanceof \WP_Error) {
            throw new Attachment_Exception('Ошибка при загрузке thumbnail. wperror: ' . $attachmentID->get_error_code() . ' ' . $attachmentID->get_error_message() . ' ' . $attachmentID->get_error_data());
        }
        if (!is_numeric($attachmentID)) {
            throw new Attachment_Exception('Ошибка при загрузке thumbnail. $attachmentID: ' . $attachmentID);
        }
        if (!set_post_thumbnail($postId, $attachmentID)) {
            throw new Attachment_Exception('Ошибка set_post_thumbnail $postId: ' . $postId . ', $attachmentID: ' . $attachmentID);
        }
        $this->setThumbnailUrl($postId, $url);
    }

    private function insertDb(array $args, Post $xmlPost): int
    {
        // есть slug?
        if ($xmlPost->getSlug() !== NULL) {
            $this->_logs[] = 'Есть slug ' . $xmlPost->getSlug() . ', xml post id: ';
            $args['post_name'] = $xmlPost->getSlug(); // Альтернативное название записи (slug) будет использовано в УРЛе.
        }

        // сбор мета в массив
        $metas = array(
            self::WP_POST_META_KEY_ID => $xmlPost->getID() // уникальный id этого поста точно нужен в meta
        );
        if ($xmlPost->getMetas()->count() > 0) {
            $this->_logs[] = 'Есть мета: ' . $xmlPost->getMetas()->count();
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
            $this->_logs[] = 'Есть теги: ' . $xmlPost->getTags()->count();
            foreach ($xmlPost->getTags()->get() as $tag) {
                $tags[] = $tag;
            }
        }

        // вставка, изменение и сбор категорий в массив
        $categories = [];
        if ($xmlPost->getCategories()->count() > 0) {
            $this->_logs[] = 'Есть категории: ' . $xmlPost->getCategories()->count();
            foreach ($xmlPost->getCategories()->get() as $category) {
                try {
                    $wpCategories = $this->_importCategory->getWPCategoriesByXMLCategoryKey($category->getKey());
                    foreach ($wpCategories as $wpCategory) {
                        $categories[] = $this->_importCategory->update($wpCategory, $category->getValue());
                    }
                } catch (Wp_Category_Not_Found_Exception $e) {
                    $categories[] = $this->_importCategory->insert($category->getKey(), $category->getValue());
                }
            }
        }
        if (!empty($categories)) {
            $args['post_category'] = $categories; // Категории к которой относится пост.
        }
        $args['tags_input'] = $tags; // Метки поста (указываем ярлыки, имена или ID).
        $args['meta_input'] = $metas; // добавит указанные мета поля. По умолчанию: ''. с версии 4.4.
        $postID = wp_insert_post(wp_slash($args));
        if (!is_numeric($postID)) {
            throw new Exception('Ошибка при получении id только что вставленного поста');
        }
        $this->_logs[] = 'ID поста в WP : ' . $postID;
        return $postID;
    }

}
