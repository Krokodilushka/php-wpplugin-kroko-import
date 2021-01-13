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

class Import_Post extends Loggable {

	const WP_POST_META_KEY_ID = 'kroko_import_post_id';
	const WP_POST_THUMBNAIL_URL_META_KEY_ID = 'kroko_import_thumbnail_url';
	const DEFAULT_POST_ARGS = [
		'comment_status' => 'open', // 'closed' означает, что комментарии закрыты.
		'ping_status'    => 'closed', // 'closed' означает, что пинги и уведомления выключены.
		'post_author'    => 1, // id автора
		'post_status'    => 'publish', // Статус создаваемой записи.
		'post_type'      => 'post', // Тип записи.
	];

	private $_importCategory;

	function __construct( array &$_logs, Import_Category $import_category ) {
		parent::__construct( $_logs );
		$this->_import_category = $import_category;
	}

	public function get_wp_post_by_xml_post_id( string $xmlPostId ): WP_Post {
		$wpPost = get_posts( array(
			'meta_key'   => self::WP_POST_META_KEY_ID,
			'meta_value' => $xmlPostId,
			'post_type'  => 'post',
		) );
		if ( ! empty( $wpPost ) ) {
			return current( $wpPost );
		} else {
			throw new Wp_Post_Not_Found_Exception( 'Wp post by xml post ID ' . $xmlPostId . ' not found' );
		}
	}

	public function insert( Post $xml_post ): int {
		$args                 = self::DEFAULT_POST_ARGS;
		$args['post_title']   = $xml_post->get_title();
		$args['post_content'] = $xml_post->get_content();
		$args['post_date']    = $xml_post->get_date()->format( 'Y-m-d H:i:s' );

		return $this->insert_db( $args, $xml_post );
	}

	public function update( WP_Post $wp_post, Post $xml_post ): int {
		$args                 = self::DEFAULT_POST_ARGS;
		$args['post_title']   = $xml_post->get_title();
		$args['post_content'] = $xml_post->get_content();
		$args['ID']           = $wp_post->ID;
		$args['post_date']    = $wp_post->post_date;

		return $this->insert_db( $args, $xml_post );
	}

	public function get_thumbnail_url( int $postId ): ?string {
		return get_post_meta( $postId, self::WP_POST_THUMBNAIL_URL_META_KEY_ID, true );
	}

	public function set_thumbnail_url( string $postId, string $url ): void {
		update_post_meta( $postId, self::WP_POST_THUMBNAIL_URL_META_KEY_ID, $url );
	}

	public function delete_post_thumbnail( string $postId ): void {
		delete_post_thumbnail( $postId );
		delete_post_meta( $postId, self::WP_POST_THUMBNAIL_URL_META_KEY_ID );
	}

	public function set_post_thumbnail( string $post_id, string $url, ?string $description ) {
		$attachment_id = media_sideload_image( $url, $post_id, $description, 'id' );
		if ( $attachment_id instanceof \WP_Error ) {
			throw new Attachment_Exception( 'Ошибка при загрузке thumbnail. wperror: ' . $attachment_id->get_error_code() . ' ' . $attachment_id->get_error_message() . ' ' . $attachment_id->get_error_data() );
		}
		if ( ! is_numeric( $attachment_id ) ) {
			throw new Attachment_Exception( 'Ошибка при загрузке thumbnail. $attachment_id: ' . $attachment_id );
		}
		if ( ! set_post_thumbnail( $post_id, $attachment_id ) ) {
			throw new Attachment_Exception( 'Ошибка set_post_thumbnail $postId: ' . $post_id . ', $attachment_id: ' . $attachment_id );
		}
		$this->set_thumbnail_url( $post_id, $url );
	}

	private function insert_db( array $args, Post $xml_post ): int {
		// есть slug?
		if ( $xml_post->get_slug() !== null ) {
			$this->_logs[]     = 'Есть slug ' . $xml_post->get_slug() . ', xml post id: ';
			$args['post_name'] = $xml_post->get_slug(); // Альтернативное название записи (slug) будет использовано в УРЛе.
		}

		// сбор мета в массив
		$metas = array(
			self::WP_POST_META_KEY_ID => $xml_post->get_id() // уникальный id этого поста точно нужен в meta
		);
		if ( $xml_post->get_metas()->count() > 0 ) {
			$this->_logs[] = 'Есть мета: ' . $xml_post->get_metas()->count();
			foreach ( $xml_post->get_metas()->get() as $meta ) {
				// если в xml мета использовался ключ, который используется для идентификации поста, то кинуть исключение
				if ( $meta->get_key() == self::WP_POST_META_KEY_ID ) {
					throw new \Exception( 'Illegal meta ' . self::WP_POST_META_KEY_ID . '' );
				}
				$metas[ $meta->get_key() ] = $meta->get_value();
			}
		}

		// сбор тегов в массив
		$tags = array();
		if ( $xml_post->get_tags()->count() > 0 ) {
			$this->_logs[] = 'Есть теги: ' . $xml_post->get_tags()->count();
			foreach ( $xml_post->get_tags()->get() as $tag ) {
				$tags[] = $tag;
			}
		}

		// вставка, изменение и сбор категорий в массив
		$categories = [];
		if ( $xml_post->get_categories()->count() > 0 ) {
			$this->_logs[] = 'Есть категории: ' . $xml_post->get_categories()->count();
			foreach ( $xml_post->get_categories()->get() as $category ) {
				try {
					$wpCategories = $this->_importCategory->getWPCategoriesByXMLCategoryKey( $category->get_key() );
					foreach ( $wpCategories as $wpCategory ) {
						$categories[] = $this->_importCategory->update( $wpCategory, $category->get_value() );
					}
				} catch ( Wp_Category_Not_Found_Exception $e ) {
					$categories[] = $this->_importCategory->insert( $category->get_key(), $category->get_value() );
				}
			}
		}
		if ( ! empty( $categories ) ) {
			$args['post_category'] = $categories; // Категории к которой относится пост.
		}
		$args['tags_input'] = $tags; // Метки поста (указываем ярлыки, имена или ID).
		$args['meta_input'] = $metas; // добавит указанные мета поля. По умолчанию: ''. с версии 4.4.
		$post_id            = wp_insert_post( wp_slash( $args ) );
		if ( ! is_numeric( $post_id ) ) {
			throw new Exception( 'Ошибка при получении id только что вставленного поста' );
		}
		$this->_logs[] = 'ID поста в WP : ' . $post_id;

		return $post_id;
	}

}
