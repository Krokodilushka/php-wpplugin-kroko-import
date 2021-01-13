<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Data\Feed_Options;
use KrokoImport\Data\XML\Comment;
use KrokoImport\Data\XML\Post;
use KrokoImport\Exceptions\Wp_Comment_Not_Found_Exception;
use KrokoImport\Exceptions\Wp_Post_Not_Found_Exception;
use KrokoImport\Model\xml_Parser;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import
{

    private $_logs = [];
    private $_import_post;

    function __construct()
    {
        if ($role = get_role('administrator')) $role->add_cap('unfiltered_upload');
        $import_category       = new Import_Category($this->_logs);
        $this->_import_post   = new Import_Post($this->_logs, $import_category);
        $this->_import_comment = new Import_Comment($this->_logs);
    }

    public function get_logs(): array
    {
        return $this->_logs;
    }

    public function clear_logs(): void
    {
        $this->_logs = [];
    }

    public function process_feed(Feed_Options $feed_options)
    {
        $xml = xml_Parser::load($feed_options->getUrl());
        $parsed = xml_Parser::parse($xml);
        $save_at_once = $feed_options->getSaveAtOnce();
        if (!empty($parsed->countPosts())) {
            $new_posts = 0;
            foreach ($parsed->getPosts() as $xmlPost) {
                $isNewPost = $this->processPost($feed_options, $xmlPost);
                if ($isNewPost) {
                    $new_posts++;
                }
                if ($save_at_once != 0 && $new_posts >= $save_at_once) {
                    $this->_logs[] = 'Добавлено ' . $new_posts . ' постов, по настройкам фида максимум можно ' . $save_at_once;
                    break;
                }
            }
        } else {
            $this->_logs[] = 'В XML нет постов';
        }
    }

    private function processPost(Feed_Options $feed_options, Post $xml_post): ?bool
    {
        $res = null;
        $wp_post_id = null;
        $this->_logs[] = 'Начало обработки XML поста ' . $xml_post->getID();
        try {
            $wpPost = $this->_import_post->getWPPostByXMLPostId($xml_post->getID());
            if (!$feed_options->getOnExistsUpdate()) {
                $this->_logs[] = 'Такой пост уже найден (' . $xml_post->getID() . '). по настройкам фида не нужно обновлять';
                return null;
            }
            $this->_logs[] = 'Обновление поста ' . $xml_post->getID();
            $wp_post_id = $this->_import_post->update($wpPost, $xml_post);
            $res = false;
        } catch (Wp_Post_Not_Found_Exception $e) {
            $this->_logs[] = 'Создание нового поста ' . $xml_post->getID();
            $wp_post_id = $this->_import_post->insert($xml_post);
            $res = true;
        }
        $this->_logs[] = 'ID текущего поста ' . $wp_post_id;

        // thumbnail
        $thumbnailIdPost = get_post_thumbnail_id($wp_post_id);
        $thumbnailMetaUrl = $this->_import_post->getThumbnailUrl($wp_post_id);
        $thumbnailXml = $xml_post->getThumbnail();
        $this->_logs[] = 'Текущий thumbnail поста: attachmentId - ' . $thumbnailIdPost . '(' . $thumbnailMetaUrl . '), должен быть: ' . $thumbnailXml;
        if ($thumbnailMetaUrl == '') {
            if ($thumbnailIdPost > 0) {
                $this->_logs[] = 'Удаление thumbnail поста ' . $wp_post_id . ' т.к в мета thumbnail ничего нет, текущий thumbnail есть';
                $this->_import_post->deletePostThumbnail($wp_post_id);
            }
        }
        if ($thumbnailMetaUrl == '' || $thumbnailMetaUrl != $thumbnailXml) {
            if (is_null($thumbnailXml)) {
                $this->_logs[] = 'Удаление thumbnail поста ' . $wp_post_id . ' т.к в xml нет thumbnail';
                $this->_import_post->deletePostThumbnail($wp_post_id);
            } else {
                $this->_logs[] = 'Установка thumbnail поста ' . $wp_post_id . ' ' . $thumbnailXml;
                $this->_import_post->setPostThumbnail($wp_post_id, $thumbnailXml, $xml_post->getTitle());
            }
        }

        // комментарии
        if (!is_null($wp_post_id)) {
            $xmlPostComments = $xml_post->getComments();
            if ($xmlPostComments->count() > 0) {
                $this->_logs[] = 'Есть комментарии для поста ' . $xml_post->getID();
                foreach ($xmlPostComments->get() as $xmlPostComment) {
                    $this->processComment($wp_post_id, null, $xmlPostComment);
                }
            }
        }
        $this->_logs[] = 'Конец обработки XML поста ' . $xml_post->getID();
        return $res;
    }

    private function processComment(int $wpPostId, ?int $wpCommentIdReply, Comment $xmlPostComment)
    {
        $wpCommentId = null;
        try {
            $wpComments = $this->_importComment->getWPCommentsByXMLCategoryKey($xmlPostComment->getID());
            foreach ($wpComments as $wpComment) {
                $this->_logs[] = 'Обновление комментария ' . $xmlPostComment->getID() . ' к посту ' . $wpPostId . ', wp comment id:' . $wpComment->comment_ID;
                $wpCommentId = $this->_importComment->update($wpPostId, $wpComment, $xmlPostComment);
            }
        } catch (Wp_Comment_Not_Found_Exception $e) {
            $this->_logs[] = 'Создание нового комментария ' . $xmlPostComment->getID() . ' к посту ' . $wpPostId;
            $wpCommentId = $this->_importComment->insert($wpPostId, $wpCommentIdReply, $xmlPostComment);
        }
        $replies = $xmlPostComment->getReplies();
        if (!is_null($wpCommentId) && !is_null($replies) && $replies->count() > 0) {
            $this->_logs[] = 'Есть вложенные комментарии к xml ID ' . $xmlPostComment->getID() . ', count: ' . $replies->count();
            foreach ($replies->get() as $replyComment) {
                $this->processComment($wpPostId, $wpCommentId, $replyComment);
            }
        }
    }

}
