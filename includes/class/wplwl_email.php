<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Store failed images so that we can manually handle them
 */
if ( ! class_exists( 'WPLWL_EMAIL_Table' ) ) {
	class WPLWL_EMAIL_Table {
		protected static $table = 'wplwl_email';
		protected static $instance = null;

		public static function get_instance( $new = false ) {
			if ( $new || null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public static function create_table() {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;

			$query = "CREATE TABLE IF NOT EXISTS {$table} (
                 `id`             bigint(20) NOT NULL AUTO_INCREMENT,
                 `email`          longtext NOT NULL,
                 `date_created`   bigint(15) NOT NULL,
                 `email_mobile`   text  NULL,
                 `email_name`     text NULL,
                 `spin_times`     longtext NULL,
                 `email_coupons`  longtext NOT NULL,
                 `email_labels`   longtext NOT NULL,
                 `custom_column`  longtext NULL,
                 `active`         int(1) default 1 not null,
                 PRIMARY KEY  (`id`)
             )";

			$wpdb->query( $query );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		public static function insert( $email, $date_created = '', $email_mobile = '', $email_name = '', $spin_times = [], $email_coupons = [], $email_labels = [], $active = 1 ) {
			if ( empty( $email ) ) {
				return false;
			}
			global $wpdb;
			$table = $wpdb->prefix . self::$table;
			if ( empty( $date_created ) ) {
				$date_created = current_time( 'U' );/*date_created is always the current timestamp of time zone GMT+0*/
			}
			$wpdb->insert( $table,//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				[
					'email'         => $email,
					'date_created'  => $date_created,
					'email_mobile'  => $email_mobile,
					'email_name'    => $email_name,
					'spin_times'    => maybe_serialize( $spin_times ),
					'email_coupons' => maybe_serialize( $email_coupons ),
					'email_labels'  => maybe_serialize( $email_labels ),
					'active'        => $active,
				],
				[ '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d' ]
			);

			return $wpdb->insert_id;
		}

		public static function update( $email, $date_created = '', $email_mobile = '', $email_name = '', $spin_times = [], $email_coupons = [], $email_labels = [], $active = 1 ) {
			if ( empty( $email ) ) {
				return false;
			}
			global $wpdb;
			$table = $wpdb->prefix . self::$table;
			if ( empty( $date_created ) ) {
				$date_created = current_time( 'U' );/*date_created is always the current timestamp of time zone GMT+0*/
			}
			$updated = $wpdb->update( $table,//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				[
					'date_created'  => $date_created,
					'email_mobile'  => $email_mobile,
					'email_name'    => $email_name,
					'spin_times'    => maybe_serialize( $spin_times ),
					'email_coupons' => maybe_serialize( $email_coupons ),
					'email_labels'  => maybe_serialize( $email_labels ),
					'active'        => $active,
				],
				[ 'email' => $email ],
				[ '%d', '%s', '%s', '%s', '%s', '%s', '%d' ],
				[ '%s' ]
			);

			return $updated;
		}

		public static function delete( $email ) {
			if ( empty( $email ) ) {
				return false;
			}
			global $wpdb;
			$table = $wpdb->prefix . self::$table;

			$delete = $wpdb->delete( $table, [ 'email' => $email, 'active' => 0, ], [ '%s' ] );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

			return $delete;
		}

		/**
		 * @param $column
		 *
		 * @return bool|int
		 */
		public static function add_column( $column ) {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;
			$query = "ALTER TABLE {$table} ADD COLUMN if NOT EXISTS `{$column}` varchar(50) default ''";

			return $wpdb->query( $query );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		/**
		 * @return bool|int
		 */
		public static function empty_table() {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;
			$query = "DELETE FROM {$table}";

			return $wpdb->query( $query );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		/**
		 * @param $column
		 * @param $datatype
		 *
		 * @return bool|int
		 */
		public static function modify_column( $column, $datatype ) {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;
			$query = "ALTER TABLE {$table} MODIFY COLUMN `{$column}` {$datatype}";

			return $wpdb->query( $query );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		/**
		 *
		 * @param $email
		 *
		 * @return array|object|void|null
		 */
		public static function get_row( $email ) {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;

			$query = "SELECT * FROM {$table} WHERE email=%s LIMIT 1";

			return $wpdb->get_row( $wpdb->prepare( $query, $email ), ARRAY_A );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		/**
		 *
		 * @param $email_id
		 *
		 * @return array|object|void|null
		 */
		public static function get_row_by_id( $email_id ) {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;

			$query = "SELECT * FROM {$table} WHERE id=%s LIMIT 1";

			return $wpdb->get_row( $wpdb->prepare( $query, $email_id ), ARRAY_A );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		/**
		 * @param int $limit
		 * @param int $offset
		 * @param bool $count
		 * @param string $active
		 *
		 * @param array $args
		 *
		 * @return array|object|string|null
		 */
		public static function get_rows( $limit = 0, $offset = 0, $count = false, $active = '', ...$args ) {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;

			$select = '*';
			if ( $count ) {
				$select = 'count(*)';
				$query  = "SELECT {$select} FROM {$table}";
				if ( $active ) {
					$query .= " WHERE {$table}.active=%d";
					$query = $wpdb->prepare( $query, $active );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				}

				return $wpdb->get_var( $query );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			} else {
				$query = "SELECT {$select} FROM {$table}";
				if ( $active ) {
					$query .= " WHERE {$table}.active=%d";
					if ( $limit ) {
						$query .= " LIMIT {$offset},{$limit}";
					}
					$query = $wpdb->prepare( $query, $active );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
				} elseif ( $limit ) {
					$query .= " LIMIT {$offset},{$limit}";
				}

				return $wpdb->get_results( $query, ARRAY_A );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}
		}
		/**
		 * Get list of emails
		 */
		public static function get_emails( $args = array() ) {
			global $wpdb;
			$table = $wpdb->prefix . self::$table;

			$args  = wp_parse_args( $args, array(
				'fields'  => '*',
				'where'   => '',
				'limit'   => 30,
				'offset'  => 0,
				'orderby' => 'id',
				'order'   => 'DESC',
			) );

			$get_column = isset( $args['fields'] ) && is_string($args['fields']) && $args['fields'] !=='*';
			if ( isset( $args['fields'] ) && is_array( $args['fields'] ) ) {
				$args['fields'] = implode( ', ', $args['fields'] );
			}
			$where = ! empty( $args['where'] ) ? "WHERE {$args['where']}" : '';
			$query = "SELECT  {$args['fields']} FROM {$table}  {$where}  ORDER BY {$args['orderby']} {$args['order']}";
			if (!empty($args['limit'])){
				$query = $wpdb->prepare( $query ." LIMIT %d OFFSET %d ", $args['limit'], $args['offset'] );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			}

			return $get_column ? $wpdb->get_col($query) : $wpdb->get_results( $query, ARRAY_A );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

	}
}
