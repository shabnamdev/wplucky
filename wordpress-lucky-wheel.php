<?php

        /**
 * Plugin Name: چرخ شانس وردپرس نگارش حرفه ای
 * Description: با چرخاندن چرخ شانس، ایمیل‌های مشتریان را جمع‌آوری کنید تا کوپن‌های تخفیف دریافت کنید.
 * Version: 2.0.0
 * Author: SHABNAM
 * Author URI: https://www.zhaket.com/store/web/shabnam
 * Text Domain: wordpress-lucky-wheel
 * Domain Path: /languages
 * Copyright 2018-2024 VillaTheme.com. All rights reserved.
 * Tested up to: 6.6.2
 * Requires PHP: 7.0
 * Requires at least: 5.0
 */

	require_once __DIR__.'/activatezhk/validate-locked.php';
if(class_exists('\d9eccf561d81573786c2e6e6bda8fd') && \d9eccf561d81573786c2e6e6bda8fd::d28c3821b404aaff8cacaa2233a()){


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! defined( 'VI_WORDPRESS_LUCKY_WHEEL_VERSION' ) ) {
	define( 'VI_WORDPRESS_LUCKY_WHEEL_VERSION', '2.0.0' );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_NAME', 'WordPress Lucky Wheel Premium' );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_BASENAME', plugin_basename( __FILE__ ) );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_DIR', plugin_dir_path( __FILE__ ) );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_INCLUDES', VI_WORDPRESS_LUCKY_WHEEL_DIR . "includes" . DIRECTORY_SEPARATOR );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_ADMIN', VI_WORDPRESS_LUCKY_WHEEL_DIR . "admin" . DIRECTORY_SEPARATOR );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_FRONTEND', VI_WORDPRESS_LUCKY_WHEEL_DIR . "frontend" . DIRECTORY_SEPARATOR );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_PLUGINS', VI_WORDPRESS_LUCKY_WHEEL_DIR . "plugins" . DIRECTORY_SEPARATOR );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_LANGUAGES', VI_WORDPRESS_LUCKY_WHEEL_DIR . "languages" . DIRECTORY_SEPARATOR );
	$plugin_url = plugins_url( '', __FILE__ );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_CSS', $plugin_url . "/css/" );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_JS', $plugin_url . "/js/" );
	define( 'VI_WORDPRESS_LUCKY_WHEEL_IMAGES', $plugin_url . "/images/" );
}
class WORDPRESS_LUCKY_WHEEL{
	public function __construct() {
		if(class_exists('\d9eccf561d81573786c2e6e6bda8fd')){
        \d9eccf561d81573786c2e6e6bda8fd::b2e14e693eed2d237749b($this);
  }
		if(class_exists('\d9eccf561d81573786c2e6e6bda8fd')){
        \d9eccf561d81573786c2e6e6bda8fd::e6a0ed6c0f27b1a00c2f5abf($this);
  }
	}
	public function check_environment( $recent_activate = false ) {
		if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
			include_once VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'support.php';
		}
		$environment = new VillaTheme_Require_Environment( [
				'plugin_name'      => VI_WORDPRESS_LUCKY_WHEEL_NAME,
				'php_version'      => '7.0',
				'wp_version'       => '5.0',
			]
		);
		if ( $environment->has_error() ) {
			return;
		}
		global $wpdb;
		$tables = array(
			'wplwl_email'    => 'wplwl_email',
		);
		foreach ( $tables as $name => $table ) {
			$wpdb->$name    = $wpdb->prefix . $table;
			$wpdb->tables[] = $table;
		}
		$this->includes();
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'plugin_action_links_' . VI_WORDPRESS_LUCKY_WHEEL_BASENAME, array( $this, 'settings_link' ) );
		if ( ! get_option( 'wpwlwl_updated_database' ) ) {
			add_filter( 'manage_wplwl_email_posts_columns', array( $this, 'add_column' ), 10, 1 );
			add_action( 'manage_wplwl_email_posts_custom_column', array( $this, 'add_column_data' ), 10, 2 );
			add_action( 'init', array( $this, 'create_custom_post_type' ) );
		}
	}
	public function create_custom_post_type() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( post_type_exists( 'wplwl_email' ) ) {
			return;
		}
		$args = array(
			'labels'              => array(
				'name'               => esc_html_x( 'Lucky Wheel Email', 'wordpress-lucky-wheel' ),
				'singular_name'      => esc_html_x( 'Email', 'wordpress-lucky-wheel' ),
				'menu_name'          => esc_html_x( 'Emails', 'Admin menu', 'wordpress-lucky-wheel' ),
				'name_admin_bar'     => esc_html_x( 'Emails', 'Add new on Admin bar', 'wordpress-lucky-wheel' ),
				'view_item'          => esc_html__( 'View Email', 'wordpress-lucky-wheel' ),
				'all_items'          => esc_html__( 'Email Subscribe', 'wordpress-lucky-wheel' ),
				'search_items'       => esc_html__( 'Search Email', 'wordpress-lucky-wheel' ),
				'parent_item_colon'  => esc_html__( 'Parent Email:', 'wordpress-lucky-wheel' ),
				'not_found'          => esc_html__( 'No Email found.', 'wordpress-lucky-wheel' ),
				'not_found_in_trash' => esc_html__( 'No Email found in Trash.', 'wordpress-lucky-wheel' )
			),
			'description'         => esc_html__( 'WordPress lucky wheel emails.', 'wordpress-lucky-wheel' ),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
		);
		register_post_type( 'wplwl_email', $args );
	}
	public function add_column( $columns ) {
		$columns['customer_name'] = esc_html__( 'Customer name', 'wordpress-lucky-wheel' );
		$columns['mobile']        = esc_html__( 'Mobile', 'wordpress-lucky-wheel' );
		$columns['total_spins']   = esc_html__( 'Total of spins', 'wordpress-lucky-wheel' );
		$columns['spins']         = esc_html__( 'Number of spins', 'wordpress-lucky-wheel' );
		$columns['last_spin']     = esc_html__( 'Last spin', 'wordpress-lucky-wheel' );
		$columns['label']         = esc_html__( 'Labels', 'wordpress-lucky-wheel' );
		$columns['coupon']        = esc_html__( 'Coupons', 'wordpress-lucky-wheel' );

		return $columns;
	}
	public function add_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'customer_name':
				if ( get_post( $post_id )->post_content ) {
					echo wp_kses_post( get_post( $post_id )->post_content );
				}
				break;
			case 'mobile':
				if ( get_post_meta( $post_id, 'wplwl_email_mobile', true ) ) {
					echo esc_html( get_post_meta( $post_id, 'wplwl_email_mobile', true ) );
				}
				break;
			case 'total_spins':
				$wplwl_spin_times = $wplwl_spin_times ?? get_post_meta( $post_id, 'wplwl_spin_times', true );
				if ( isset ($wplwl_spin_times['total_spins'] ) ) {
					echo esc_html( $wplwl_spin_times['total_spins'] );
				} else {
					echo esc_html( $wplwl_spin_times['spin_num'] ??'' );
				}
				break;
			case 'spins':
				$wplwl_spin_times = $wplwl_spin_times ?? get_post_meta( $post_id, 'wplwl_spin_times', true );
				echo esc_html( $wplwl_spin_times['spin_num'] ??'' );
				break;
			case 'last_spin':
				$wplwl_spin_times = $wplwl_spin_times ?? get_post_meta( $post_id, 'wplwl_spin_times', true );
				if (isset ($wplwl_spin_times['last_spin'] ) ) {
					echo esc_html( date( 'Y-m-d h:i:s', $wplwl_spin_times['last_spin']) );//phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				}
				break;

			case 'label':
				if ( get_post_meta( $post_id, 'wplwl_email_labels', true ) ) {
					$label = get_post_meta( $post_id, 'wplwl_email_labels', true );
					if ( sizeof( $label ) > 1 ) {
						for ( $i = sizeof( $label ) - 1; $i >= 0; $i -- ) {
							echo '<p>' . wp_kses_post( $label[ $i ] ) . '</p>';
						}
					} else {
						echo wp_kses_post( $label[0] );
					}
				}
				break;
			case 'coupon':
				if ( get_post_meta( $post_id, 'wplwl_email_coupons', true ) ) {
					$coupon = get_post_meta( $post_id, 'wplwl_email_coupons', true );
					if ( sizeof( $coupon ) > 1 ) {
						for ( $i = sizeof( $coupon ) - 1; $i >= 0; $i -- ) {
							echo '<p>' . wp_kses_post( $coupon[ $i ] ) . '</p>';
						}
					} else {
						echo wp_kses_post( $coupon[0] );
					}
				}
				break;
		}
	}
	public function settings_link( $links ) {
		$settings_link = sprintf( '<a href="%s" title="%s">%s</a>', esc_attr( admin_url( 'admin.php?page=wordpress-lucky-wheel' ) ),
			esc_attr__( 'Settings', 'wordpress-lucky-wheel' ),
			esc_html__( 'تنظیمات', 'wordpress-lucky-wheel' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	}
	public function init() {
		$this->load_plugin_textdomain();
		if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
			new VillaTheme_Support_Pro(
				array(
					'support'   => 'https://www.zhaket.com/panel/tickets/new',
					'docs'      => 'https://www.zhaket.com/store/web/shabnam',
					'review'    => 'https://www.zhaket.com/store/web/shabnam',
					'css'       => VI_WORDPRESS_LUCKY_WHEEL_CSS,
					'image'     => VI_WORDPRESS_LUCKY_WHEEL_IMAGES,
					'slug'      => 'wordpress-lucky-wheel',
					'menu_slug' => 'wordpress-lucky-wheel',
					'version'   => VI_WORDPRESS_LUCKY_WHEEL_VERSION,
				)
			);
		}
	}
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wordpress-lucky-wheel' );
		load_textdomain( 'wordpress-lucky-wheel', VI_WORDPRESS_LUCKY_WHEEL_LANGUAGES . "wordpress-lucky-wheel-$locale.mo" );
		load_plugin_textdomain( 'wordpress-lucky-wheel', false,VI_WORDPRESS_LUCKY_WHEEL_LANGUAGES );
	}
	protected function includes() {
		$files = array(
			VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'data.php',
			VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'functions.php',
			VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'support.php',
			VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'check_update.php',
			VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'update.php',
			VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'elementor/elementor.php',
		);
		foreach ( $files as $file ) {
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
		vi_include_folder( VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . "background-process" . DIRECTORY_SEPARATOR,'just_require' );
		vi_include_folder( VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . "class" . DIRECTORY_SEPARATOR, 'just_require');
		vi_include_folder( VI_WORDPRESS_LUCKY_WHEEL_ADMIN, 'VI_WORDPRESS_LUCKY_WHEEL_Admin_' );
		vi_include_folder( VI_WORDPRESS_LUCKY_WHEEL_FRONTEND, 'VI_WORDPRESS_LUCKY_WHEEL_Frontend_' );
		vi_include_folder( VI_WORDPRESS_LUCKY_WHEEL_PLUGINS, 'VI_WORDPRESS_LUCKY_WHEEL_Plugins_' );
	}
	public function after_activated( $plugin, $network_wide ) {
		if ( $plugin !== VI_WORDPRESS_LUCKY_WHEEL_BASENAME ) {
			return;
		}
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$current_blog = $wpdb->blogid;
			$blogs        = $wpdb->get_col( $wpdb->prepare('SELECT blog_id FROM %i',[$wpdb->blogs]) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

			//Multi site activate action
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog );
				$this->create_table();
			}
			switch_to_blog( $current_blog );
		} else {
			//Single site activate action
			$this->create_table();
		}
		$count_posts = array_sum( (array)wp_count_posts('wplwl_email'));
		if ( !$count_posts ) {
			update_option( 'wpwlwl_updated_database', current_time( 'U' ) );
			update_option( 'wplwl_run_action_updated', true );
		}
	}

	public function create_table() {
		if ( ! class_exists( 'WPLWL_EMAIL_Table' ) ) {
			include_once VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . "class" . DIRECTORY_SEPARATOR . 'wplwl_email.php';
		}
		WPLWL_EMAIL_Table::create_table();
		if ( ! class_exists( 'WPLWL_Mobile_Table' ) ) {
			include_once VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . "class" . DIRECTORY_SEPARATOR . 'wplwl_mobile.php';
		}
		WPLWL_Mobile_Table::create_table();
	}
}
new WORDPRESS_LUCKY_WHEEL();


}