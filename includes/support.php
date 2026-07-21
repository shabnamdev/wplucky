<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VillaTheme_Support_Pro' ) ) {

	/**
	 * Class VillaTheme_Support_Pro
	 * 1.1.9
	 *
	 */
	class VillaTheme_Support_Pro {
		protected $plugin_base_name;
		public $data;
		protected $version = '1.1.9';
		protected $ads_data;

		public function __construct( $data ) {
			$this->data               = array();
			$this->data['support']    = $data['support']??'';
			$this->data['docs']       = $data['docs']??'';
			$this->data['review']     = $data['review']??'';
			$this->data['css_url']    = $data['css']??'';
			$this->data['images_url'] = $data['image']??'';
			$this->data['slug']       = $data['slug']??'';
			$this->data['menu_slug']  = $data['menu_slug']??'';
			$this->data['version']    = isset( $data['version'] ) ? $data['version'] : '1.0.0';
			add_action( 'villatheme_support_' . $this->data['slug'], array( $this, 'villatheme_support' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9999 );
			$this->plugin_base_name = "{$this->data['slug']}/{$this->data['slug']}.php";
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			/*Add toolbar*/
			add_action( 'admin_bar_menu', array( $this, 'add_toolbar' ), 100 );
		}

		/**Add link to Documentation, Support and Reviews
		 *
		 * @param $links
		 * @param $file
		 *
		 * @return array
		 */
		public function plugin_row_meta( $links, $file ) {
			if ( $this->plugin_base_name === $file ) {
				$row_meta = array(
					'support' => sprintf( '<a href="%s" target="_blank" title="%s">%s</a>', esc_attr( esc_url( $this->data['support'] ) ), esc_attr( 'VillaTheme Support' ), esc_html( 'Support' ) ),
					'review'  => sprintf( '<a href="%s" target="_blank" title="%s">%s</a>', esc_attr( esc_url( $this->data['review'] ) ), esc_attr( 'Rate this plugin' ), esc_html( 'Reviews' ) ),
				);
				if ( ! empty( $this->data['docs'] ) ) {
					$row_meta['docs'] = sprintf( '<a href="%s" target="_blank" title="%s">%s</a>',
						esc_attr( esc_url( $this->data['docs'] ) ), esc_attr( 'Plugin Documentation' ), esc_html( 'Docs' ) );
				}

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		public function admin_init() {
			$this->hide_notices();
			$villatheme_call = get_transient( 'villatheme_call' );
			if ( ! $villatheme_call || ! is_plugin_active( "{$villatheme_call}/{$villatheme_call}.php" ) ) {
				/*Make sure ads and dashboard widget show only once when multiple VillaTheme plugins are installed*/
				set_transient( 'villatheme_call', $this->data['slug'], DAY_IN_SECONDS );
			}
			if ( get_transient( 'villatheme_call' ) == $this->data['slug'] ) {
				add_action( 'admin_notices', array( $this, 'form_ads' ) );
				/*Admin dashboard*/
				add_action( 'wp_dashboard_setup', array( $this, 'dashboard' ) );
			}
		}

		public function hide_notices() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! isset( $_GET['_villatheme_nonce'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			$_villatheme_nonce = isset( $_GET['_villatheme_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_villatheme_nonce'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( wp_verify_nonce( $_villatheme_nonce, 'villatheme_hide_toolbar' ) ) {
				update_option( 'villatheme_hide_admin_toolbar', time() );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}

			if ( wp_verify_nonce( $_villatheme_nonce, 'villatheme_show_toolbar' ) ) {
				delete_option( 'villatheme_hide_admin_toolbar' );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( $_villatheme_nonce, 'villatheme_hide_banner' ) ) {
				update_option( 'villatheme_hide_dashboard_banner', time() );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( $_villatheme_nonce, 'villatheme_show_banner' ) ) {
				delete_option( 'villatheme_hide_dashboard_banner' );
				wp_safe_redirect( esc_url_raw( remove_query_arg( array( '_villatheme_nonce' ) ) ) );
				exit();
			}
			if ( wp_verify_nonce( $_villatheme_nonce, 'hide_maybe' ) ) {
				set_transient( $this->data['slug'] . $this->data['version'] . 'hide_maybe', 1, 2592000 );
			}

			$hide_notice = isset( $_GET['villatheme-hide-notice'] ) ? sanitize_text_field( wp_unslash( $_GET['villatheme-hide-notice'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$ads_id      = isset( $_GET['ads_id'] ) ? sanitize_text_field( wp_unslash( $_GET['ads_id'] ) ) : '';// phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( empty( $_villatheme_nonce ) && empty( $hide_notice ) ) {
				return;
			}

			if ( wp_verify_nonce( $_villatheme_nonce, 'hide_notices' ) ) {
				global $current_user;

				if ( $hide_notice == 1 ) {
					if ( $ads_id ) {
						update_option( 'villatheme_hide_notices_' . $ads_id, time() + DAY_IN_SECONDS );
					} else {
						set_transient( 'villatheme_hide_notices_' . $current_user->ID, 1, DAY_IN_SECONDS );
					}
				} else {
					if ( $ads_id ) {
						update_option( 'villatheme_hide_notices_' . $ads_id, $ads_id );
					} else {
						set_transient( 'villatheme_hide_notices_' . $current_user->ID, 1, DAY_IN_SECONDS * 30 );
					}
				}
			}
		}

		/**
		 * Add Extension page
		 */
		public function admin_menu() {
			add_submenu_page( $this->data['menu_slug'],
				esc_html( '' ), esc_html( '' ), 'manage_options', $this->data['slug'] . '-extensions', array(
					$this,
					'page_callback'
				) );
		}

		/**
		 * Extensions page
		 */
		public function page_callback() {
			$ads = '';
			?>
            <div class="villatheme-extension-page">
                <div class="villatheme-extension-top">
                </div>
                <div class="villatheme-extension-content">
					<?php
					$feeds = get_transient( 'villatheme_ads' );
					if ( ! $feeds ) {
						try {
							$request_data = $this->wp_remote_get( false );
							if ( isset($request_data['status']) && $request_data['status'] === 'success' ) {
								$ads = $request_data['data'];
							}
							set_transient( 'villatheme_ads', $ads, DAY_IN_SECONDS );
						} catch ( Exception $e ) {
						}

					} else {
						$ads = $feeds;
					}
					if ( $ads ) {
						$ads = json_decode( $ads );
						$ads = array_filter( $ads );
					} else {
						return;
					}
					if ( is_array( $ads ) && count( $ads ) ) {
						foreach ( $ads as $ad ) {
							?>

								<?php
								if ( $ad->title ) { ?>
                                    <div class="villatheme-item-title">
										<?php
										if ( $ad->link ) { ?>
                                        <a target="_blank"
                                           href="<?php echo esc_attr( esc_url( $ad->link ) ) ?>">
											<?php } ?>
											<?php if ( $ad->link ) { ?>
                                        </a>
									<?php
									}
									?>
                                    </div>
									<?php
								}
								?>
                                <div class="villatheme-item-controls">
                                    <div class="villatheme-item-controls-inner">
										<?php
										if ( $ad->link ) {
											?>
                                          
											<?php
										}
										if ( $ad->demo_url ) {
											?>

											<?php
										}
										if ( $ad->free_url ) {
											?>

											<?php
										}
										?>
                                    </div>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                </div>
            </div>
			<?php
		}

		/**
		 * Init script
		 */
		public function scripts() {
			if ( ! wp_style_is( 'villatheme-support' ) ) {
				wp_enqueue_style( 'villatheme-support', $this->data['css_url'] . 'villatheme-support.min.css', '', $this->version );
				wp_add_inline_script( 'admin-bar', "(function ($) {
                    $(function () {
                        $(document).on('click','#wp-admin-bar-villatheme_hide_toolbar',function(e){
                            if (!confirm('VillaTheme toolbar helps you access all VillaTheme items quickly, do you want to hide it anyway?')){
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }
                        });
                    });
                }(jQuery));" );
			}
		}

		/**
		 *
		 */
		public function villatheme_support() {
			$hide_banner = get_option( 'villatheme_hide_dashboard_banner' );
			?>
          
				<?php
				if ( ! $hide_banner ) {
					?>
                    <div class="villatheme-items">
						<?php
						$items = $this->get_data( $this->data['slug'] );
						if ( is_array( $items ) && count( $items ) ) {
							shuffle( $items );
							$items = array_slice( $items, 0, 12 );
							foreach ( $items as $k => $item ) {
								?>
								<?php
							}
						}
						?>
                    </div>
					<?php
				}
				?>
            </div>
			<?php
		}

		/**
		 * Get data from server
		 *
		 * @param bool $slug
		 *
		 * @return array
		 */
		protected function get_data( $slug = false ) {
			$ads   = '';
			$feeds = get_transient( 'villatheme_ads' );
			if ( ! $feeds ) {
				try {
					$request_data = $this->wp_remote_get( false );
					if ( isset($request_data['status'])&& $request_data['status'] === 'success' ) {
						$ads = $request_data['data'];
					}
					set_transient( 'villatheme_ads', $ads, DAY_IN_SECONDS );
				} catch ( Exception $e ) {
				}
			} else {
				$ads = $feeds;
			}
			$results = array();
			if ( $ads ) {
				$ads = json_decode( $ads );
				$ads = array_filter( $ads );
				foreach ( $ads as $ad ) {
					if ( $slug ) {
						if ( $ad->slug == $slug ) {
							continue;
						}
					}
					$item        = new stdClass();
					$item->title = $ad->title;
					$item->link  = $ad->link;
					$item->thumb = $ad->thumb;
					$item->image = $ad->image;
					$item->desc  = $ad->description;
					$results[]   = $item;
				}
			}

			return $results;
		}

		/**
		 * Add toolbar in WordPress Dashboard
		 */
		public function add_toolbar() {
			/**
			 * @var $wp_admin_bar WP_Admin_Bar
			 */
			global $wp_admin_bar;
			if ( get_option( 'villatheme_hide_admin_toolbar' ) ) {
				return;
			}
			if ( ! $wp_admin_bar->get_node( 'villatheme' ) ) {
				$wp_admin_bar->add_node( array(
					'id'    => 'villatheme',
					'title' => wp_kses_post( '<span class="ab-icon dashicons-star-filled villatheme-rotating"></span>شبنم' ),
					'href'  => '',
					'meta'  => array(
						'class' => 'villatheme-toolbar'
					),
				) );
				add_action( 'admin_bar_menu', array( $this, 'hide_toolbar_button' ), 200 );
			}
			if ( $this->data['menu_slug'] ) {
				$wp_admin_bar->add_node( array(
					'id'     => $this->data['slug'],
					'title'  => $this->get_plugin_name(),
					'parent' => 'villatheme',
					'href'   => strpos( $this->data['menu_slug'], '.php' ) === false ? admin_url( 'admin.php?page=' . $this->data['menu_slug'] ) : admin_url( $this->data['menu_slug'] ),
				) );
			}
		}

		public function hide_toolbar_button() {
			global $wp_admin_bar;
			/**
			 * @var $wp_admin_bar WP_Admin_Bar
			 */
			$wp_admin_bar->add_node( array(
				'id'     => 'villatheme_hide_toolbar',
				'title'  => '<span class="dashicons dashicons-dismiss"></span><span class="villatheme-hide-toolbar-button-title">پنهان سازی نوار ابزار گردونه شانس شبنم</span>',
				'parent' => 'villatheme',
				'href'   => add_query_arg( array( '_villatheme_nonce' => wp_create_nonce( 'villatheme_hide_toolbar' ) ) ),
			) );
		}

		private function get_plugin_name() {
			$plugins = get_plugins();

			return isset( $plugins[ $this->plugin_base_name ]['Title'] ) ? $plugins[ $this->plugin_base_name ]['Title'] : ucwords( str_replace( '-', ' ', $this->data['slug'] ) );
		}

		/**
		 * Dashboard widget
		 */
		public function dashboard() {
			$this->get_ads_data();
			if ( $this->ads_data === false ) {
				return;
			}
			wp_add_dashboard_widget( 'villatheme_dashboard_status', esc_html( 'VillaTheme News' ), array( $this, 'widget' ) );
		}

		public function widget() {
			?>
            <div class="villatheme-dashboard">
                <div class="villatheme-content">
					<?php
					if ( !empty($this->ads_data['heading'] ) ) { ?>
                        <h3><?php echo esc_html( $this->ads_data['heading'] ) ?></h3>
						<?php
					}
					if ( !empty($this->ads_data['description']) ) { ?>
                        <p><?php echo esc_html( $this->ads_data['description'] ) ?></p>
						<?php
					}
					?>
                    <p>
						<?php
						if ( !empty($this->ads_data['link'] )) {
							?>
                            <a target="_blank" href="<?php echo esc_url( $this->ads_data['link'] ); ?>"
                               class="button button-primary"><?php echo esc_html( 'Get Your Gift' ) ?></a>
							<?php
						}
						?>
                    </p>
                </div>
            </div>
			<?php
		}

		/**
		 * Show Notices
		 */
		public function form_ads() {
			$this->get_ads_data();
			if ( $this->ads_data === false ) {
				return;
			}
			ob_start(); ?>
            <div class="villatheme-dashboard updated">
                <div class="villatheme-content">
					<?php
					if ( !empty($this->ads_data['heading']) ) { ?>
                        <h3><?php echo esc_html( $this->ads_data['heading'] ) ?></h3>
						<?php
					}
					if ( !empty($this->ads_data['description'] )) { ?>
                        <p><?php echo esc_html( $this->ads_data['description'] ) ?></p>
						<?php
					}
					?>
                    <p>
                        <a target="_self"
                           href="<?php echo esc_url( wp_nonce_url( add_query_arg( array(
							   'villatheme-hide-notice' => '2',
							   'ads_id'                 => $this->ads_data['id']??'',
						   ) ), 'hide_notices', '_villatheme_nonce' ) ); ?>"
                           class="button notice-dismiss vi-button-dismiss"><?php echo esc_html( 'Dismiss' ) ?></a>
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array(
							'villatheme-hide-notice' => '1',
							'ads_id'                 => $this->ads_data['id']??'',
						) ), 'hide_notices', '_villatheme_nonce' ) ); ?>"
                           class="button"><?php echo esc_html( 'Thanks, later.' ) ?></a>
						<?php
						if (!empty( $this->ads_data['link'] )) { ?>
                            <a target="_blank" href="<?php echo esc_url( $this->ads_data['link'] ); ?>"
                               class="button button-primary"><?php echo esc_html( 'Get Your Gift' ) ?></a>
							<?php
						}
						?>
                    </p>
                </div>
            </div>
			<?php
			echo wp_kses_post( apply_filters( 'form_ads_data', ob_get_clean() ) );
		}

		public function get_ads_data() {
			global $current_user;
			if ( $this->ads_data !== null ) {
				return;
			}
			$this->ads_data = false;
			if ( get_transient( 'villatheme_hide_notices_' . $current_user->ID ) ) {
				return;
			}
			$data   = get_transient( 'villatheme_notices' );
			$called = get_transient( 'villatheme_called' );
			if ( ! $data && ! $called ) {
				$request_data = $this->wp_remote_get( true );
				if ( isset($request_data['status']) && $request_data['status'] === 'success' ) {
					$data = json_decode( $request_data['data']??'{}', true );
				}
				set_transient( 'villatheme_notices', $data, DAY_IN_SECONDS );
			}

			if ( ! $called ) {
				set_transient( 'villatheme_called', 1, DAY_IN_SECONDS );
			}

			if ( ! is_array( $data ) ) {
				return;
			}
			$data = wp_parse_args( $data, array(
				'heading'     => '',
				'description' => '',
				'link'        => '',
				'id'          => '',
			) );
			if ( ! $data['heading'] && ! $data['description'] ) {
				return;
			}
			$getdate      = getdate();
			$current_time = $getdate[0];
			if ( isset( $data['start'] ) && strtotime( $data['start'] ) > $current_time ) {
				return;
			}
			if ( isset( $data['end'] ) && strtotime( $data['end'] ) < $current_time ) {
				return;
			}
			if ( isset( $data['loop'] ) && $data['loop'] ) {
				if ( ! in_array( $getdate['wday'], explode( ',', $data['loop'] ) ) ) {
					return;
				}
			}
			if ( $data['id'] ) {
				$hide = get_option( 'villatheme_hide_notices_' . $data['id'] );
				if ( $hide === $data['id'] || time() < intval( $hide ) ) {
					return;
				}
			}
			$this->ads_data = $data;
		}

		/**
		 * Get latest VillaTheme plugins and ads
		 * Available information is appended to changelog of some plugins, which is available with plugins_api()
		 *
		 * @param $is_ads
		 *
		 * @return array
		 */
		public function wp_remote_get( $is_ads = false ) {
			$return = array(
				'status' => 'error',
				'data'   => '',
			);
			include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			foreach ( [ 'woo-multi-currency', 'email-template-customizer-for-woo' ] as $slug ) {
				$api = plugins_api( 'plugin_information', [ 'slug' => $slug, 'locale' => 'en_US' ] );
				if ( ! is_wp_error( $api ) ) {
					if ( isset( $api->sections, $api->sections['changelog'] ) ) {
						$changelog = $api->sections['changelog'];
						if ( $changelog ) {
							if ( $is_ads ) {
								preg_match( '/VillaThemeCampaign:{(.*)}/', $changelog, $match );
							} else {
								preg_match( '/VillaThemePlugins:\[(.*)]/sm', $changelog, $match );
							}
							if ( $match ) {
								$json = html_entity_decode( str_replace( array(
									'&#8222;',
									'&#8221;',
									'&#8220;',
									'&#8243;',
									'„',
								), '"', $match[1] ) );
								if ( $is_ads ) {
									$json = '{' . $json . '}';
								} else {
									$json = '[' . $json . ']';
								}
								$return['data']   = $json;
								$return['status'] = 'success';
								break;
							}
						}
					}
				}
			}

			return $return;
		}
	}
}

if ( ! class_exists( 'VillaTheme_Require_Environment' ) ) {
	class VillaTheme_Require_Environment {

		protected $args;
		protected $plugin_name;
		protected $notices = [];

		public function __construct( $args ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				_doing_it_wrong( 'VillaTheme_Require_Environment', wp_kses_post( 'VillaTheme_Require_Environment should not be run before the %s hook.<code>plugins_loaded</code>' ), '1.1.9' );
			}

			$args = apply_filters( 'villatheme_check_requires', wp_parse_args( $args, [
				'plugin_name'     => '',
				'php_version'     => '',
				'wp_version'      => '',
				'wc_version'      => '',
				'require_plugins' => [],
			] ) );

			$this->plugin_name = $args['plugin_name'];

			$this->check( $args );

			add_action( 'admin_notices', [ $this, 'notice' ] );
		}

		protected function check( $args ) {
			if ( ! function_exists( 'install_plugin_install_status' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! empty( $args['php_version'] ) ) {
				$compatible_php = is_php_version_compatible( $args['php_version'] );
				if ( ! $compatible_php ) {
					$this->notices[] = sprintf( "PHP version at least %s.", esc_html( $args['php_version'] ) );
				}
			}

			if ( ! empty( $args['wp_version'] ) ) {
				$compatible_wp = is_wp_version_compatible( $args['wp_version'] );
				if ( ! $compatible_wp ) {
					$this->notices[] = sprintf( "WordPress version at least %s.", esc_html( $args['wp_version'] ) );
				}
			}

			if ( ! empty( $args['require_plugins'] ) ) {
				foreach ( $args['require_plugins'] as $plugin ) {
					if ( empty( $plugin['version'] ) ) {
						$plugin['version'] = '';
					}

					$status              = install_plugin_install_status( $plugin );
					$require_plugin_name = $plugin['name'] ?? '';

					if ( ! empty( $plugin['requires_php'] ) && ! is_php_version_compatible( $plugin['requires_php'] ) ) {
						continue;
					}

					if ( ! empty( $plugin['requires'] ) && ! is_wp_version_compatible( $plugin['requires'] ) ) {
						continue;
					}
					$plugin_slug = $plugin['slug'];

					switch ( $status['status'] ??'') {

						case 'install':
							$this->notices[] = sprintf( "%s to be installed. <br><a href='%s' target='_blank' class='button button-primary' style='vertical-align: middle; margin-top: 5px;'>Install %s</a>",
								esc_html( $require_plugin_name ),
								esc_url( current_user_can( 'install_plugins' ) ? wp_nonce_url( network_admin_url( "update.php?action=install-plugin&plugin={$plugin_slug}" ), "install-plugin_{$plugin_slug}" ) : '#' ),
								esc_html( $require_plugin_name ) );

							break;

						default:

							if ( !empty($status['file']) && ! is_plugin_active( $status['file'] ) ) {
								$msg = sprintf( '%s is installed and activated.', esc_html( $require_plugin_name ) );
								if ( current_user_can( 'activate_plugin', $status['file'] ) ) {
									$activate_url = add_query_arg(
										[
											'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
											'action'   => 'activate',
											'plugin'   => $status['file'],
										],
										self_admin_url( 'plugins.php' )
									);

									$msg .= sprintf( " <br> <a href='%s' target='_blank' class='button button-primary' style='vertical-align: middle; margin-top: 5px;'>Active %s</a>",
										esc_url( $activate_url ),
										esc_html( $require_plugin_name ) );
								}
								$this->notices[] = $msg;

							}

							if ( ! empty( $plugin['required_version'] ) && ! empty( $status['version'] ) ) {
								if ( ! version_compare( $status['version'], $plugin['required_version'], '>=' ) ) {
									$this->notices[] = sprintf( "%s version at least %s.", esc_html( $require_plugin_name ), esc_html( $plugin['required_version'] ) );
								}
							}

							if (!empty($plugin['slug']) &&  $plugin['slug'] == 'woocommerce' && ! empty( $args['wc_version'] ) && is_plugin_active( $status['file']??'' ) ) {
								$wc_current_version = get_option( 'woocommerce_version' );
								if ( ! version_compare( $wc_current_version, $args['wc_version'], '>=' ) ) {
									$this->notices[] = sprintf( "WooCommerce version at least %s.", esc_html( $args['wc_version'] ) );
								}
							}

							break;
					}
				}
			}
		}

		public function notice() {
			$screen = get_current_screen();

			if ( ! current_user_can( 'manage_options' ) || $screen->id === 'update' ) {
				return;
			}

			if ( ! empty( $this->notices ) ) {
				?>
                <div class="error">
					<?php
					if ( count( $this->notices ) > 1 ) {
						printf( "<p>%s requires:</p>", esc_html( $this->plugin_name ) );
						?>
                        <ol>
							<?php
							foreach ( $this->notices as $notice ) {
								printf( "<li>%s</li>", wp_kses_post( $notice ) );
							}
							?>
                        </ol>
						<?php
					} else {
						printf( "<p>%s requires %s</p>", esc_html( $this->plugin_name ), wp_kses_post( current( $this->notices ) ) );
					}
					?>
                </div>
				<?php
			}
		}

		public function has_error() {
			return ! empty( $this->notices );
		}
	}
}