<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Data\XML\Comment;
use KrokoImport\Exceptions\Wp_Comment_Not_Found_Exception;
use WP_Comment;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import_Comment extends Loggable
{

    const WP_COMMENT_META_KEY_ID = 'kroko_import_comment_id';

    public function getWPCommentsByXMLCategoryKey(string $xmlCommentId): array
    {
        $wpComments = get_comments(array(
            'meta_key' => self::WP_COMMENT_META_KEY_ID,
            'meta_value' => $xmlCommentId,
        ));
        if (empty($wpComments)) {
            throw new Wp_Comment_Not_Found_Exception('Комментарий с xml id ' . $xmlCommentId . ' не найден');
        }
        return $wpComments;
    }

    public function insert(string $postId, ?int $replyTo, Comment $comment): int
    {
        $args = $this->getDefaultArgs($postId, $comment);
        $args['comment_date'] = $comment->getDate()->format('Y-m-d H:i:s');
        $args['comment_meta'][self::WP_COMMENT_META_KEY_ID] = $comment->getID();
        $args['comment_approved'] = 1;
        if (!is_null($replyTo)) {
            $args['comment_parent'] = $replyTo;
        }
        $commentID = wp_insert_comment($args);
        return $commentID;
    }

    public function update(string $postId, WP_Comment $wpComment, Comment $comment): int
    {
        $args = $this->getDefaultArgs($postId, $comment);
        $args['comment_ID'] = $wpComment->comment_ID;
        wp_update_comment($args, true);
        return $args['comment_ID'];
    }

    private function getDefaultArgs(string $postId, Comment $comment): array
    {
        return [
            'comment_post_ID' => $postId,
            'comment_author' => $comment->getAuthor(),
            'comment_author_email' => md5($comment->getAuthor()) . '@kroko-import.test',
            'comment_content' => $comment->getText(),
            'comment_parent' => 0,
            'comment_meta' => []
        ];
    }

}
