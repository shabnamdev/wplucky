<?php
defined( 'ABSPATH' ) || exit;

final class WPLWL_Post {

	public $ID;
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $post_name = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_parent = 0;
	public $post_type = 'wplwl_email';
	public $filter;
	protected static $new_table = null;

	public function __construct( $post ) {
		foreach ( get_object_vars( $post ) as $key => $value ) {
			$this->$key = $value;
		}
	}

	public static function get_posts( $args ) {
		if ( ! self::use_custom_table() ) {
			$the_query = new \WP_Query( $args );
			$posts     = $the_query->get_posts();
			wp_reset_postdata();

			return $posts;
		}
		$limit = $args['posts_per_page'] ?? 0;
		$where = [];
		if ( ! empty( $args['date_query'][0]['before'] ) ) {
			$where[] = 'date_created <= ' . strtotime( $args['date_query'][0]['before'] );
		}
		if ( ! empty( $args['date_query'][0]['after'] ) ) {
			$where[] = 'date_created >= ' . strtotime( $args['date_query'][0]['after'] );
		}
		$args1 = [
			'fields' => '*',
			'limit'  => $limit > 0 ? $limit : 0,
			'where'  => implode( ' AND ', $where ),
			'offset' => $args['offset'] ?? 0,
		];

		return WPLWL_EMAIL_Table::get_emails( $args1 );
	}

	public static function use_custom_table() {
		if ( self::$new_table !== null ) {
			return self::$new_table;
		}
		$migrated = get_option( 'wpwlwl_updated_database' );
		if ( $migrated ) {
			self::$new_table = true;
		} else {
			self::$new_table = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance()->get_params( 'use_custom_table' );
		}

		return self::$new_table;
	}

	public static function get_instance( $post_id ) {
		if ( is_a( $post_id, 'WP_Post' ) ) {
			return $post_id;
		}
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return false;
		}
		$_post = wp_cache_get( $post_id, 'wplwl_email' );
		if ( ! $_post ) {
			$_post = WPLWL_EMAIL_Table::get_row_by_id( $post_id );
			if ( empty( $_post ) ) {
				return false;
			}
			$_post['ID']            = $_post['date_created']??'';
			$_post['post_title']    = $_post['email']??'';
			$_post['post_content']  = $_post['email_name']??'';
			$_post['email_coupons'] = maybe_unserialize( $_post['email_coupons'] ??'');
			$_post['spin_times']    = maybe_unserialize( $_post['spin_times'] ??'');
			$_post['email_labels']  = maybe_unserialize( $_post['email_labels']??'' );
			$_post                  = (object) sanitize_post( $_post, 'raw' );
			wp_cache_add( $_post->ID, $_post, 'wplwl_email' );
		} elseif ( empty( $_post->filter ) || 'raw' !== $_post->filter ) {
			$_post = sanitize_post( $_post, 'raw' );
		}

		return new WP_Post( (object) $_post );
	}
}