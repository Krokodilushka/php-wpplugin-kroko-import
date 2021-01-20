<?php

namespace KrokoImport\Model\Import;

use KrokoImport\Data\Feed_Options;
use KrokoImport\Data\XML\Comment;
use KrokoImport\Data\XML\Feed;
use KrokoImport\Data\XML\Post;
use KrokoImport\Exceptions\Wp_Comment_Not_Found_Exception;
use KrokoImport\Exceptions\Wp_Post_Not_Found_Exception;
use KrokoImport\Model\xml_Parser;

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

class Import {

	private $_logs = [];
	private $_import_post;

	function __construct() {
		if ( $role = get_role( 'administrator' ) ) {
			$role->add_cap( 'unfiltered_upload' );
		}
		$import_category       = new Import_Category( $this->_logs );
		$this->_import_post    = new Import_Post( $this->_logs, $import_category );
		$this->_import_comment = new Import_Comment( $this->_logs );
	}

	public function get_logs(): array {
		return $this->_logs;
	}

	public function clear_logs(): void {
		$this->_logs = [];
	}

	public function parseUrl( string $url ): Feed {
		$xml    = xml_Parser::load( $url );
		$parsed = xml_Parser::parse( $xml );

		return $parsed;
	}

	public function process_feed( Feed_Options $feed_options ) {
		$parsed       = $this->parseUrl( $feed_options->get_url() );
		$save_at_once = $feed_options->get_save_at_once();
		if ( ! empty( $parsed->count_posts() ) ) {
			$new_posts = 0;
			foreach ( $parsed->get_posts() as $xmlPost ) {
				$isNewPost = $this->process_post( $feed_options->get_on_exists_update(), $xmlPost );
				if ( $isNewPost ) {
					$new_posts ++;
				}
				if ( $save_at_once != 0 && $new_posts >= $save_at_once ) {
					$this->_logs[] = 'Добавлено ' . $new_posts . ' постов, по настройкам фида максимум можно ' . $save_at_once;
					break;
				}
			}
		} else {
			$this->_logs[] = 'В XML нет постов';
		}
	}

	public function process_post( bool $on_exists_update, Post $xml_post ): ?bool {
		$res           = null;
		$wp_post_id    = null;
		$this->_logs[] = 'Начало обработки XML поста ' . $xml_post->get_id();
		try {
			$wp_post = $this->_import_post->get_wp_post_by_xml_post_id( $xml_post->get_id() );
			if ( ! $on_exists_update ) {
				$this->_logs[] = 'Такой пост уже найден (' . $xml_post->get_id() . '). по настройкам фида не нужно обновлять';
				return null;
			}
			$this->_logs[] = 'Обновление поста ' . $xml_post->get_id();
			$wp_post_id    = $this->_import_post->update( $wp_post, $xml_post );
			$res           = false;
		} catch ( Wp_Post_Not_Found_Exception $e ) {
			$this->_logs[] = 'Создание нового поста ' . $xml_post->get_id();
			$wp_post_id    = $this->_import_post->insert( $xml_post );
			$res           = true;
		}
		$this->_logs[] = 'ID текущего поста ' . $wp_post_id;

		// thumbnail
		$thumbnail_id_post  = get_post_thumbnail_id( $wp_post_id );
		$thumbnail_meta_url = $this->_import_post->get_thumbnail_url( $wp_post_id );
		$thumbnail_xml      = $xml_post->get_thumbnail();
		$this->_logs[]      = 'Текущий thumbnail поста: attachmentId - ' . $thumbnail_id_post . '(' . $thumbnail_meta_url . '), должен быть: ' . $thumbnail_xml;
		if ( $thumbnail_meta_url == '' ) {
			if ( $thumbnail_id_post > 0 ) {
				$this->_logs[] = 'Удаление thumbnail поста ' . $wp_post_id . ' т.к в мета thumbnail ничего нет, текущий thumbnail есть';
				$this->_import_post->delete_post_thumbnail( $wp_post_id );
			}
		}
		if ( $thumbnail_meta_url == '' || $thumbnail_meta_url != $thumbnail_xml ) {
			if ( is_null( $thumbnail_xml ) ) {
				$this->_logs[] = 'Удаление thumbnail поста ' . $wp_post_id . ' т.к в xml нет thumbnail';
				$this->_import_post->delete_post_thumbnail( $wp_post_id );
			} else {
				$this->_logs[] = 'Установка thumbnail поста ' . $wp_post_id . ' ' . $thumbnail_xml;
				$this->_import_post->set_post_thumbnail( $wp_post_id, $thumbnail_xml, $xml_post->get_title() );
			}
		}

		// комментарии
		if ( ! is_null( $wp_post_id ) ) {
			$xml_post_comments = $xml_post->get_comments();
			if ( $xml_post_comments->count() > 0 ) {
				$this->_logs[] = 'Есть комментарии для поста ' . $xml_post->get_id();
				foreach ( $xml_post_comments->get() as $xml_post_comment ) {
					$this->processComment( $wp_post_id, null, $xml_post_comment );
				}
			}
		}
		$this->_logs[] = 'Конец обработки XML поста ' . $xml_post->get_id();

		return $res;
	}

	private function processComment( int $wp_post_id, ?int $wp_comment_id_reply, Comment $xml_post_comment ) {
		$wp_comment_id = null;
		try {
			$wp_comments = $this->_import_comment->get_wp_comments_by_xml_category_key( $xml_post_comment->get_id() );
			foreach ( $wp_comments as $wp_comment ) {
				$this->_logs[] = 'Обновление комментария ' . $xml_post_comment->get_id() . ' к посту ' . $wp_post_id . ', wp comment id:' . $wp_comment->comment_ID;
				$wp_comment_id = $this->_import_comment->update( $wp_post_id, $wp_comment, $xml_post_comment );
			}
		} catch ( Wp_Comment_Not_Found_Exception $e ) {
			$this->_logs[] = 'Создание нового комментария ' . $xml_post_comment->get_id() . ' к посту ' . $wp_post_id;
			$wp_comment_id = $this->_import_comment->insert( $wp_post_id, $wp_comment_id_reply, $xml_post_comment );
		}
		$replies = $xml_post_comment->get_replies();
		if ( ! is_null( $wp_comment_id ) && ! is_null( $replies ) && $replies->count() > 0 ) {
			$this->_logs[] = 'Есть вложенные комментарии к xml ID ' . $xml_post_comment->get_id() . ', count: ' . $replies->count();
			foreach ( $replies->get() as $replyComment ) {
				$this->processComment( $wp_post_id, $wp_comment_id, $replyComment );
			}
		}
	}


}
