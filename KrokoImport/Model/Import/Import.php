<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Data\FeedOptions;
use KrokoImport\Data\XML\Comment;
use KrokoImport\Data\XML\Post;
use KrokoImport\Exceptions\WpCommentNotFoundException;
use KrokoImport\Exceptions\WpPostNotFoundException;
use KrokoImport\Model\XMLParser;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import
{

    private $_logs = [];
    private $_importPost;

    function __construct()
    {
        if ($role = get_role('administrator')) $role->add_cap('unfiltered_upload');
        $importCategory = new ImportCategory($this->_logs);
        $this->_importPost = new ImportPost($this->_logs, $importCategory);
        $this->_importComment = new ImportComment($this->_logs);
    }

    public function getLogs(): array
    {
        return $this->_logs;
    }

    public function clearLogs(): void
    {
        $this->_logs = [];
    }

    public function processFeed(FeedOptions $feedOptions)
    {
        $xml = XMLParser::load($feedOptions->getUrl());
        $parsed = XMLParser::parse($xml);
        $saveAtOnce = $feedOptions->getSaveAtOnce();
        if (!empty($parsed->countPosts())) {
            $newPosts = 0;
            foreach ($parsed->getPosts() as $xmlPost) {
                $isNewPost = $this->processPost($feedOptions, $xmlPost);
                if ($isNewPost) {
                    $newPosts++;
                }
                if ($saveAtOnce != 0 && $newPosts >= $saveAtOnce) {
                    $this->_logs[] = 'Добавлено ' . $newPosts . ' постов, по настройкам фида максимум можно ' . $saveAtOnce;
                    break;
                }
            }
        } else {
            $this->_logs[] = 'В XML нет постов';
        }
    }

    private function processPost(FeedOptions $feedOptions, Post $xmlPost): ?bool
    {
        $res = null;
        $wpPostId = null;
        $this->_logs[] = 'Начало обработки XML поста ' . $xmlPost->getID();
        try {
            $wpPost = $this->_importPost->getWPPostByXMLPostId($xmlPost->getID());
            if (!$feedOptions->getOnExistsUpdate()) {
                $this->_logs[] = 'Такой пост уже найден (' . $xmlPost->getID() . '). по настройкам фида не нужно обновлять';
                return null;
            }
            $this->_logs[] = 'Обновление поста ' . $xmlPost->getID();
            $wpPostId = $this->_importPost->update($wpPost, $xmlPost);
            $res = false;
        } catch (WpPostNotFoundException $e) {
            $this->_logs[] = 'Создание нового поста ' . $xmlPost->getID();
            $wpPostId = $this->_importPost->insert($xmlPost);
            $res = true;
        }
        $this->_logs[] = 'ID текущего поста ' . $wpPostId;

        // thumbnail
        $thumbnailIdPost = get_post_thumbnail_id($wpPostId);
        $thumbnailMetaUrl = $this->_importPost->getThumbnailUrl($wpPostId);
        $thumbnailXml = $xmlPost->getThumbnail();
        $this->_logs[] = 'Текущий thumbnail поста: attachmentId - ' . $thumbnailIdPost . '(' . $thumbnailMetaUrl . '), должен быть: ' . $thumbnailXml;
        if ($thumbnailMetaUrl == '') {
            if ($thumbnailIdPost > 0) {
                $this->_logs[] = 'Удаление thumbnail поста ' . $wpPostId . ' т.к в мета thumbnail ничего нет, текущий thumbnail есть';
                $this->_importPost->deletePostThumbnail($wpPostId);
            }
        }
        if ($thumbnailMetaUrl == '' || $thumbnailMetaUrl != $thumbnailXml) {
            if (is_null($thumbnailXml)) {
                $this->_logs[] = 'Удаление thumbnail поста ' . $wpPostId . ' т.к в xml нет thumbnail';
                $this->_importPost->deletePostThumbnail($wpPostId);
            } else {
                $this->_logs[] = 'Установка thumbnail поста ' . $wpPostId . ' ' . $thumbnailXml;
                $this->_importPost->setPostThumbnail($wpPostId, $thumbnailXml, $xmlPost->getTitle());
            }
        }

        // комментарии
        if (!is_null($wpPostId)) {
            $xmlPostComments = $xmlPost->getComments();
            if ($xmlPostComments->count() > 0) {
                $this->_logs[] = 'Есть комментарии для поста ' . $xmlPost->getID();
                foreach ($xmlPostComments->get() as $xmlPostComment) {
                    $this->processComment($wpPostId, null, $xmlPostComment);
                }
            }
        }
        $this->_logs[] = 'Конец обработки XML поста ' . $xmlPost->getID();
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
        } catch (WpCommentNotFoundException $e) {
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
