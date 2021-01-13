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

    public function get_wp_comments_by_xml_category_key(string $xmlCommentId): array
    {
        $wp_comments = get_comments(array(
            'meta_key' => self::WP_COMMENT_META_KEY_ID,
            'meta_value' => $xmlCommentId,
        ));
        if (empty($wp_comments)) {
            throw new Wp_Comment_Not_Found_Exception('Комментарий с xml id ' . $xmlCommentId . ' не найден');
        }
        return $wp_comments;
    }

    public function insert(string $postId, ?int $reply_to, Comment $comment): int
    {
        $args = $this->get_default_args($postId, $comment);
        $args['comment_date'] = $comment->get_date()->format('Y-m-d H:i:s');
        $args['comment_meta'][self::WP_COMMENT_META_KEY_ID] = $comment->get_id();
        $args['comment_approved'] = 1;
        if (!is_null($reply_to)) {
            $args['comment_parent'] = $reply_to;
        }
        $comment_id = wp_insert_comment($args);
        return $comment_id;
    }

    public function update(string $post_id, WP_Comment $wp_comment, Comment $comment): int
    {
        $args = $this->get_default_args($post_id, $comment);
        $args['comment_ID'] = $wp_comment->comment_ID;
        wp_update_comment($args, true);
        return $args['comment_ID'];
    }

    private function get_default_args(string $post_id, Comment $comment): array
    {
        return [
            'comment_post_ID' => $post_id,
            'comment_author' => $comment->get_author(),
            'comment_author_email' => md5($comment->get_author()) . '@kroko-import.test',
            'comment_content' => $comment->get_text(),
            'comment_parent' => 0,
            'comment_meta' => []
        ];
    }

}
