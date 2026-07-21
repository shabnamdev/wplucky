<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPLWL_Mobile_Table' ) ) {
	class WPLWL_Mobile_Table {
		const TABLE       = 'wplwl_mobile';
		const DB_VERSION  = '1.2.0';
		const VERSION_KEY = 'wplwl_mobile_db_version';

		protected static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public static function table_name() {
			global $wpdb;
			return $wpdb->prefix . self::TABLE;
		}

		public static function maybe_create_table() {
			if ( self::DB_VERSION !== get_option( self::VERSION_KEY ) ) {
				self::create_table();
			}
		}

		public static function create_table() {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$table   = self::table_name();
			$charset = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mobile_e164 varchar(16) NOT NULL,
				mobile_national varchar(11) NOT NULL,
				customer_name varchar(190) NOT NULL DEFAULT '',
				campaign_key varchar(64) NOT NULL DEFAULT 'default',
				spin_num int(10) unsigned NOT NULL DEFAULT 0,
				total_spins int(10) unsigned NOT NULL DEFAULT 0,
				last_spin bigint(15) unsigned NOT NULL DEFAULT 0,
				date_created bigint(15) unsigned NOT NULL DEFAULT 0,
				date_updated bigint(15) unsigned NOT NULL DEFAULT 0,
				mobile_coupons longtext NULL,
				mobile_labels longtext NULL,
				mobile_prizes longtext NULL,
				verified tinyint(1) unsigned NOT NULL DEFAULT 0,
				verified_at bigint(15) unsigned NOT NULL DEFAULT 0,
				marketing_consent tinyint(1) unsigned NOT NULL DEFAULT 0,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				active tinyint(1) unsigned NOT NULL DEFAULT 1,
				PRIMARY KEY  (id),
				UNIQUE KEY mobile_campaign (mobile_e164,campaign_key),
				KEY mobile_national (mobile_national),
				KEY campaign_key (campaign_key),
				KEY last_spin (last_spin),
				KEY user_id (user_id)
			) {$charset};";
			dbDelta( $sql );
			self::merge_legacy_campaign_rows();
			update_option( self::VERSION_KEY, self::DB_VERSION, false );
		}


		




		protected static function merge_legacy_campaign_rows() {
			global $wpdb;
			$table = self::table_name();
			$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY mobile_e164 ASC, id ASC", ARRAY_A );  
			if ( ! is_array( $rows ) || ! $rows ) {
				return;
			}
			$groups = array();
			foreach ( $rows as $row ) {
				$groups[ $row['mobile_e164'] ][] = $row;
			}
			foreach ( $groups as $mobile_rows ) {
				$base = null;
				foreach ( $mobile_rows as $row ) {
					if ( 'default' === $row['campaign_key'] ) {
						$base = $row;
						break;
					}
				}
				if ( null === $base ) {
					$base = $mobile_rows[0];
				}
				$coupons = array();
				$labels  = array();
				$prizes  = array();
				$delete  = array();
				foreach ( $mobile_rows as $row ) {
					$row_coupons = maybe_unserialize( $row['mobile_coupons'] );
					$row_labels  = maybe_unserialize( $row['mobile_labels'] );
					$row_prizes  = maybe_unserialize( $row['mobile_prizes'] );
					if ( is_array( $row_coupons ) ) {
						$coupons = array_merge( $coupons, $row_coupons );
					}
					if ( is_array( $row_labels ) ) {
						$labels = array_merge( $labels, $row_labels );
					}
					if ( is_array( $row_prizes ) ) {
						$prizes = array_merge( $prizes, $row_prizes );
					}
					if ( ! $base['customer_name'] && $row['customer_name'] ) {
						$base['customer_name'] = $row['customer_name'];
					}
					$base['spin_num']          += (int) $row['spin_num'] * ( (int) $row['id'] !== (int) $base['id'] ? 1 : 0 );
					$base['total_spins']       += (int) $row['total_spins'] * ( (int) $row['id'] !== (int) $base['id'] ? 1 : 0 );
					$base['last_spin']          = max( (int) $base['last_spin'], (int) $row['last_spin'] );
					$base['date_created']       = min( array_filter( array( (int) $base['date_created'], (int) $row['date_created'] ) ) ?: array( 0 ) );
					$base['date_updated']       = max( (int) $base['date_updated'], (int) $row['date_updated'] );
					$base['verified']           = max( (int) $base['verified'], (int) $row['verified'] );
					$base['verified_at']        = max( (int) $base['verified_at'], (int) $row['verified_at'] );
					$base['marketing_consent'] = max( (int) $base['marketing_consent'], (int) $row['marketing_consent'] );
					$base['user_id']            = (int) $base['user_id'] ?: (int) $row['user_id'];
					if ( (int) $row['id'] !== (int) $base['id'] ) {
						$delete[] = (int) $row['id'];
					}
				}
				if ( $delete ) {
					$wpdb->query( "DELETE FROM {$table} WHERE id IN (" . implode( ',', array_map( 'absint', $delete ) ) . ')' );  
				}
				$wpdb->update( $table, array(
					'campaign_key'       => 'default',
					'customer_name'      => $base['customer_name'],
					'spin_num'           => $base['spin_num'],
					'total_spins'        => $base['total_spins'],
					'last_spin'          => $base['last_spin'],
					'date_created'       => $base['date_created'],
					'date_updated'       => $base['date_updated'],
					'mobile_coupons'     => maybe_serialize( $coupons ),
					'mobile_labels'      => maybe_serialize( $labels ),
					'mobile_prizes'      => maybe_serialize( $prizes ),
					'verified'           => $base['verified'],
					'verified_at'        => $base['verified_at'],
					'marketing_consent'  => $base['marketing_consent'],
					'user_id'            => $base['user_id'],
				), array( 'id' => (int) $base['id'] ) );
			}
		}

		public function get_row( $mobile, $campaign = 'default' ) {
			global $wpdb;
			$table = self::table_name();
			$sql   = $wpdb->prepare( "SELECT * FROM {$table} WHERE mobile_e164 = %s ORDER BY id ASC LIMIT 1", $mobile );
			return $wpdb->get_row( $sql, ARRAY_A );  
		}

		public function insert( $data ) {
			global $wpdb;
			$table = self::table_name();
			$defaults = array(
				'mobile_e164' => '', 'mobile_national' => '', 'customer_name' => '', 'campaign_key' => 'default',
				'spin_num' => 0, 'total_spins' => 0, 'last_spin' => 0, 'date_created' => 0, 'date_updated' => 0,
				'mobile_coupons' => array(), 'mobile_labels' => array(), 'mobile_prizes' => array(), 'verified' => 0, 'verified_at' => 0,
				'marketing_consent' => 0, 'user_id' => 0, 'active' => 1,
			);
			$data = wp_parse_args( $data, $defaults );
			$data['campaign_key'] = 'default';
			$data['mobile_coupons'] = maybe_serialize( $data['mobile_coupons'] );
			$data['mobile_labels']  = maybe_serialize( $data['mobile_labels'] );
			$data['mobile_prizes']  = maybe_serialize( $data['mobile_prizes'] );
			$result = $wpdb->insert( $table, $data, array( '%s','%s','%s','%s','%d','%d','%d','%d','%d','%s','%s','%s','%d','%d','%d','%d','%d' ) );
			return false === $result ? false : $wpdb->insert_id;
		}

		public function update_by_id( $id, $data ) {
			global $wpdb;
			$table = self::table_name();
			if ( isset( $data['mobile_coupons'] ) ) {
				$data['mobile_coupons'] = maybe_serialize( $data['mobile_coupons'] );
			}
			if ( isset( $data['mobile_labels'] ) ) {
				$data['mobile_labels'] = maybe_serialize( $data['mobile_labels'] );
			}
			if ( isset( $data['mobile_prizes'] ) ) {
				$data['mobile_prizes'] = maybe_serialize( $data['mobile_prizes'] );
			}
			return $wpdb->update( $table, $data, array( 'id' => absint( $id ) ) );
		}

		public function reset_spin_counts() {
			global $wpdb;
			$table = self::table_name();
			return $wpdb->query( "UPDATE {$table} SET spin_num = 0" );  
		}

		public function query( $args = array() ) {
			global $wpdb;
			$table = self::table_name();
			$args = wp_parse_args( $args, array(
				'search' => '', 'limit' => 30, 'offset' => 0, 'orderby' => 'id', 'order' => 'DESC', 'count' => false,
			) );
			$allowed_orderby = array( 'id', 'date_created', 'date_updated', 'last_spin', 'total_spins', 'mobile_e164' );
			$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'id';
			$order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';
			$where   = array( 'active = 1' );
			$values  = array();
			if ( $args['search'] ) {
				$like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
				$where[] = '(mobile_e164 LIKE %s OR mobile_national LIKE %s OR customer_name LIKE %s)';
				$values = array_merge( $values, array( $like, $like, $like ) );
			}
			$select = $args['count'] ? 'COUNT(*)' : '*';
			$sql = "SELECT {$select} FROM {$table} WHERE " . implode( ' AND ', $where );
			if ( ! $args['count'] ) {
				$sql .= " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
				$values[] = absint( $args['limit'] );
				$values[] = absint( $args['offset'] );
			}
			if ( $values ) {
				$sql = $wpdb->prepare( $sql, $values );
			}
			return $args['count'] ? (int) $wpdb->get_var( $sql ) : $wpdb->get_results( $sql, ARRAY_A );  
		}
	}
}
