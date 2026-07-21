<?php

/**
 * Class VI_WORDPRESS_LUCKY_WHEEL_Frontend_Shortcode
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WORDPRESS_LUCKY_WHEEL_Frontend_Shortcode {
	protected $settings;
	protected $language;
	protected $prefix;
	protected $pointer_position;

	public function __construct() {
		$this->settings = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance();
		$this->language = '';
		$this->prefix   = 'wp-lucky-wheel-shortcode-';
		if ( 'on' == $this->settings->get_params( 'general', 'enable' ) ) {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'elementor/frontend/after_enqueue_scripts', array(
				$this,
				'wp_enqueue_scripts_elementor'
			), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 99 );
		}
	}


	public function set( $name ) {
		if ( is_array( $name ) ) {
			return implode( ' ', array_map( array( $this, 'set' ), $name ) );
		} else {
			return esc_attr( $this->prefix . $name );
		}
	}

	public function init() {
		add_shortcode( 'wordpress_lucky_wheel', array( $this, 'wordpress_lucky_wheel' ) );
	}

	public function wp_enqueue_scripts_elementor() {
		if ( ! wp_script_is( 'wordpress-lucky-wheel-shortcode' ) ) {
			wp_enqueue_style( 'wordpress-lucky-wheel-shortcode', VI_WORDPRESS_LUCKY_WHEEL_CSS . 'shortcode.css', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
			wp_enqueue_script( 'wordpress-lucky-wheel-shortcode', VI_WORDPRESS_LUCKY_WHEEL_JS . 'shortcode.js', array( 'jquery' ), VI_WORDPRESS_LUCKY_WHEEL_VERSION ,true);
		}
	}

	public function wp_enqueue_scripts() {
		if ( ! wp_script_is( 'wordpress-lucky-wheel-shortcode', 'registered' ) ) {
			wp_register_style( 'wordpress-lucky-wheel-shortcode', VI_WORDPRESS_LUCKY_WHEEL_CSS . 'shortcode.css', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
			wp_register_script( 'wordpress-lucky-wheel-shortcode', VI_WORDPRESS_LUCKY_WHEEL_JS . 'shortcode.js', array( 'jquery' ), VI_WORDPRESS_LUCKY_WHEEL_VERSION,true );
			if ( $this->settings->get_params( 'wplwl_recaptcha' ) ) {
				if ( $this->settings->get_params( 'wplwl_recaptcha_version' ) == 2 ) {
					?>
                    <script src='https://www.google.com/recaptcha/api.js?hl=<?php echo esc_attr( $this->language ? $this->language : get_locale() ) ?>&render=explicit'
                            async
                            defer></script>
					<?php
				} elseif ( $this->settings->get_params( 'wplwl_recaptcha_site_key' ) ) {
					?>
                    <script src="https://www.google.com/recaptcha/api.js?hl=<?php echo esc_attr( $this->language ? $this->language : get_locale() ) ?>&render=<?php echo esc_attr($this->settings->get_params( 'wplwl_recaptcha_site_key' )); ?>"></script>
					<?php
				}
			}
		}
	}

	function is_valid_url( $url ) {

		// Must start with http:// or https://.
		if ( 0 !== strpos( $url, 'http://' ) && 0 !== strpos( $url, 'https://' ) ) {
			return false;
		}

		// Must pass validation.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return true;
	}

	public function wordpress_lucky_wheel( $atts ) {
		global $wplwl_shortcode_id;

		if ( $wplwl_shortcode_id === null ) {
			$wplwl_shortcode_id = 1;
		} else {
			$wplwl_shortcode_id ++;
		}

		$shortcode_id     = "wordpress-lucky-wheel-shortcode-{$wplwl_shortcode_id}";
		$shortcode_id_css = "#{$shortcode_id}.wp-lucky-wheel-shortcode-container";
		$args             = shortcode_atts(
			array(
				'bg_image'                          => $this->settings->get_params( 'wheel_wrap', 'bg_image' ),
				'bg_color'                          => $this->settings->get_params( 'wheel_wrap', 'bg_color' ),
				'text_color'                        => $this->settings->get_params( 'wheel_wrap', 'text_color' ),
				'pointer_color'                     => $this->settings->get_params( 'wheel_wrap', 'pointer_color' ),
				'spin_button_color'                 => $this->settings->get_params( 'wheel_wrap', 'spin_button_color' ),
				'pointer_position'                  => $this->settings->get_params( 'wheel_wrap', 'pointer_position' ),
				'spin_button_bg_color'              => $this->settings->get_params( 'wheel_wrap', 'spin_button_bg_color' ),
				'wheel_dot_color'                   => $this->settings->get_params( 'wheel_wrap', 'wheel_dot_color' ),
				'wheel_border_color'                => $this->settings->get_params( 'wheel_wrap', 'wheel_border_color' ),
				'wheel_center_color'                => $this->settings->get_params( 'wheel_wrap', 'wheel_center_color' ),
				'spinning_time'                     => $this->settings->get_params( 'wheel', 'spinning_time' ),
				'wheel_speed'                       => $this->settings->get_params( 'wheel', 'wheel_speed' ),
				'custom_field_name_enable'          => $this->settings->get_params( 'custom_field_name_enable' ),
				'custom_field_name_enable_mobile'   => $this->settings->get_params( 'custom_field_name_enable_mobile' ),
				'custom_field_name_required'        => $this->settings->get_params( 'custom_field_name_required' ),
				'custom_field_mobile_enable'        => $this->settings->get_params( 'custom_field_mobile_enable' ),
				'custom_field_mobile_enable_mobile' => $this->settings->get_params( 'custom_field_mobile_enable_mobile' ),
				'custom_field_mobile_required'      => $this->settings->get_params( 'custom_field_mobile_required' ),
				'font_size'                         => $this->settings->get_params( 'wheel', 'font_size' ),
				'wheel_size'                        => $this->settings->get_params( 'wheel', 'wheel_size' ),
				'congratulations_effect'            => $this->settings->get_params( 'wheel_wrap', 'congratulations_effect' ),
				'center_image'                      => wp_get_attachment_url( $this->settings->get_params( 'wheel_wrap', 'wheel_center_image' ) ),
				'class'                             => '',
				'is_elementor'                      => 'no',
				'wplwl_recaptcha_site_key'          => $this->settings->get_params( 'wplwl_recaptcha_site_key' ),
				'wplwl_recaptcha_version'           => $this->settings->get_params( 'wplwl_recaptcha_version' ),
				'wplwl_recaptcha_secret_theme'      => $this->settings->get_params( 'wplwl_recaptcha_secret_theme' ),
				'wplwl_recaptcha'                   => $this->settings->get_params( 'wplwl_recaptcha' ),
				'wplwl_skip_enter_email'            => $this->settings->get_params( 'custom_field_email_enable' ) ? false : true
			), $atts );
		if ( ! wp_script_is( 'wordpress-lucky-wheel-shortcode' ) ) {
			wp_enqueue_style( 'wordpress-lucky-wheel-shortcode' );
			wp_enqueue_script( 'wordpress-lucky-wheel-shortcode' );
			if ( $this->settings->get_params( 'wheel_wrap', 'congratulations_effect' ) == 'firework' ) {
				if ( ! wp_style_is( 'wordpress-lucky-wheel-frontend-style-firework' ) ) {
					wp_enqueue_style( 'wordpress-lucky-wheel-frontend-style-firework', VI_WORDPRESS_LUCKY_WHEEL_CSS . 'firework.css', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
				}
			}
		}
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$default_lang     = apply_filters( 'wpml_default_language', null );
			$current_language = apply_filters( 'wpml_current_language', null );

			if ( $current_language && $current_language !== $default_lang ) {
				$this->language = $current_language;
			}
		} else if ( class_exists( 'Polylang' ) ) {
			$default_lang     = pll_default_language( 'slug' );
			$current_language = pll_current_language( 'slug' );
			if ( $current_language && $current_language !== $default_lang ) {
				$this->language = $current_language;
			}
		}
		$wheel           = $this->settings->get_params( 'wheel' );
		$wplwl_recaptcha = $this->settings->get_params( 'wplwl_recaptcha' );
        if (!isset( $wheel['prize_type']) || !is_array( $wheel['prize_type'])){
	        $wheel['prize_type'] = [];
        }
        if (!isset(  $wheel['probability']) || !is_array( $wheel['probability'])){
	        $wheel['probability'] = [];
        }
		$coupon_count    = count( $wheel['prize_type'] );
		$prize_quantity  = $this->settings->get_params( 'wheel', 'prize_quantity' );
		if ( !is_array( $prize_quantity)){
			$prize_quantity = [];
		}
		$custom_label    = $this->settings->get_params( 'wheel', 'custom_label', $this->language );
		$quantity_label  = $this->settings->get_params( 'wheel', 'quantity_label', $this->language );
		if ( count( $prize_quantity ) !== $coupon_count ) {
			$prize_quantity = array_fill( 0, $coupon_count, - 1 );
		}
		$label       = array();
		$non         = 0;
		$probability = 0;
		foreach ( $wheel['prize_type'] as $count => $v ) {
			$wheel_label      = $custom_label[ $count ];
			$quantity_label_1 = '';
			if ( $wheel['prize_type'][ $count ] === 'non' ) {
				$non ++;
				$probability += absint( $wheel['probability'][ $count ] ?? 0);
			} else {
				if ( $prize_quantity[ $count ] != 0 ) {
					$probability += absint( $wheel['probability'][ $count ]??0 );
				}
				if ( $prize_quantity[ $count ] > 0 ) {
					$quantity_label_1 = str_replace( '{prize_quantity}', $prize_quantity[ $count ], $quantity_label );
				}
			}
			$wheel_label = str_replace( '{quantity_label}', $quantity_label_1, $wheel_label );
			$wheel_label = str_replace( array( '{prize_value}' ), '', $wheel_label );
			$label[]     = $wheel_label;
		}
		$wheel['label'] = $label;

		if ( $non === $coupon_count || $probability === 0 ) {
			return '';
		}
		/*css*/
		if ( $args['is_elementor'] !== 'yes' ) {
			$css = "{$shortcode_id_css}{";
			if ( $args['bg_image'] ) {
				$bg_image_url = $this->is_valid_url( $args['bg_image'] ) ? $args['bg_image'] : wp_get_attachment_url( $args['bg_image'] );
				if ( $bg_image_url ) {
					$css .= 'background-image:url("' . $bg_image_url . '");';
				}
			}
			if ( $args['bg_color'] ) {
				$css .= 'background-color:' . $args['bg_color'] . ';';
			}

			if ( $args['text_color'] ) {
				$css .= 'color:' . $args['text_color'] . ';';
			}
			$css .= '}';

			if ( $args['pointer_color'] ) {
				$css .= "{$shortcode_id_css} .wp-lucky-wheel-shortcode-wheel-pointer:before{color: {$args['pointer_color']};}";
				$css .= "@media(max-width:640px){{$shortcode_id_css} .wp-lucky-wheel-shortcode-wheel-pointer:before{color: {$args['pointer_color']};background-color: {$args['pointer_color']};}}";
			}
			//wheel wrap design
			$css .= "{$shortcode_id_css} .wp-lucky-wheel-shortcode-wheel-button-wrap{";
			if ( $args['spin_button_color'] ) {
				$css .= 'color:' . $args['spin_button_color'] . ';';
			}

			if ( $args['spin_button_bg_color'] ) {
				$css .= 'background-color:' . $args['spin_button_bg_color'] . ';';
			}
			$css .= '}';

			wp_register_style( 'wordpress-lucky-wheel-shortcode-style-' . $wplwl_shortcode_id, false , array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION);
			wp_enqueue_style( 'wordpress-lucky-wheel-shortcode-style-' . $wplwl_shortcode_id );
			wp_add_inline_style( 'wordpress-lucky-wheel-shortcode-style-' . $wplwl_shortcode_id, $css );
		}
		/*params*/
		$this->pointer_position = $args['pointer_position'];
		if ( $this->pointer_position == 'random' ) {
			$pointer_positions      = array(
				'center',
				'top',
				'right',
				'bottom',
			);
			$ran                    = wp_rand( 0, 3 );
			$this->pointer_position = $pointer_positions[ $ran ];
		}
		$args            = wp_parse_args( $args, array(
			'ajaxurl'                      => $this->settings->get_params( 'ajax_endpoint' ) == 'ajax' ? ( admin_url( 'admin-ajax.php?action=wplwl_get_email' ) ) : site_url() . '/wp-json/wordpress_lucky_wheel/spin',
			'pointer_position'             => $this->pointer_position,
			'gdpr'                         => $this->settings->get_params( 'wheel_wrap', 'gdpr' ),
			'gdpr_warning'                 => esc_html__( 'Please agree with our term and condition.', 'wordpress-lucky-wheel' ),
			'color'                        => $this->settings->get_params( 'wheel', 'random_color' ) == 'on' ? $this->settings::get_random_color() : ($wheel['bg_color']??''),
			'slices_text_color'            => $this->settings->get_params( 'wheel', 'slices_text_color' ),
			'prize_type'                   => $wheel['prize_type'],
			'label'                        => $label,
			'empty_email_warning'          => esc_html__( '*Please enter your email', 'wordpress-lucky-wheel' ),
			'invalid_email_warning'        => esc_html__( '*Please enter a valid email address', 'wordpress-lucky-wheel' ),
			'custom_field_name_message'    => esc_html__( '*Name is required!', 'wordpress-lucky-wheel' ),
			'custom_field_mobile_message'  => esc_html__( '*Mobile is required!', 'wordpress-lucky-wheel' ),
			'language'                     => $this->language,
			'wplwl_recaptcha_site_key'     => $this->settings->get_params( 'wplwl_recaptcha_site_key' ),
			'wplwl_recaptcha_version'      => $this->settings->get_params( 'wplwl_recaptcha_version' ),
			'wplwl_recaptcha_secret_theme' => $this->settings->get_params( 'wplwl_recaptcha_secret_theme' ),
			'wplwl_recaptcha'              => $this->settings->get_params( 'wplwl_recaptcha' ),
			'wplwl_skip_enter_email'       => $this->settings->get_params( 'custom_field_email_enable' ) ? false : true
		) );
		$container_class = array( 'container', 'pointer-position-' . $this->pointer_position );
		if ( $this->pointer_position !== 'center' ) {
			$container_class[] = 'margin-position';
		}
		$shortcode_class = $this->set( $container_class );
		if ( $args['class'] ) {
			$shortcode_class .= ' ' . $args['class'];
		}
		ob_start();
		?>
        <div id="<?php echo esc_attr( $shortcode_id ) ?>" class="<?php echo esc_attr( $shortcode_class ) ?>"
             data-shortcode_args="<?php echo esc_attr( wp_json_encode( $args ) ) ?>">
            <div class="<?php echo esc_attr( $this->set( 'wheel-container' ) ) ?>">
                <div class="<?php echo esc_attr( $this->set( 'wheel-canvas' ) ) ?>">
                    <canvas class="<?php echo esc_attr( $this->set( 'wheel-canvas-1' ) ) ?>"
                            id="<?php echo esc_attr( $this->set( 'wheel-canvas-1' ) ) ?>">
                    </canvas>
                    <canvas class="<?php echo esc_attr( $this->set( 'wheel-canvas-2' ) ) ?>"
                            id="<?php echo esc_attr( $this->set( 'wheel-canvas-2' ) ) ?>">
                    </canvas>
                    <canvas class="<?php echo esc_attr( $this->set( 'wheel-canvas-3' ) ) ?>">
                    </canvas>
                    <div class="<?php echo esc_attr( $this->set( 'wheel-pointer-container' ) ) ?>">
                        <div class="<?php echo esc_attr( $this->set( 'wheel-pointer-before' ) ) ?>">
                        </div>
                        <div class="<?php echo esc_attr( $this->set( 'wheel-pointer-main' ) ) ?>">
                            <span class="wplwl-location <?php echo esc_attr( $this->set( array(
	                            'wheel-pointer',
	                            'wheel-pointer-' . $this->pointer_position
                            ) ) ) ?>"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wp-lucky-wheel-shortcode-content-container">
                <div class="wp-lucky-wheel-shortcode-wheel-description">
					<?php echo do_shortcode( $this->settings->get_params( 'wheel_wrap', 'description', $this->language ) ); ?>
                </div>
                <div class="wplwl-congratulations-effect">
                    <div class="wplwl-congratulations-effect-before"></div>
                    <div class="wplwl-congratulations-effect-after"></div>
                </div>
                <div class="wp-lucky-wheel-shortcode-wheel-fields-container">
					<?php
					if ( 'on' == $args['custom_field_name_enable'] ) {
						?>
                        <div class="<?php echo esc_attr( $this->set( array(
							'wp-lucky-wheel-shortcode-wheel-field-name-wrap',
							'wp-lucky-wheel-shortcode-wheel-field-wrap'
						) ) ) ?>">
                            <input type="text"
                                   class="wp-lucky-wheel-shortcode-wheel-field-name wp-lucky-wheel-shortcode-wheel-field"
                                   placeholder="<?php esc_attr_e( 'Please enter your name', 'wordpress-lucky-wheel' ) ?>">
                        </div>
						<?php
					}
					if ( 'on' == $args['custom_field_mobile_enable'] ) {
						?>
                        <div class="wp-lucky-wheel-shortcode-wheel-field-mobile-wrap wp-lucky-wheel-shortcode-wheel-field-wrap">
                            <input type="text"
                                   class="wp-lucky-wheel-shortcode-wheel-field-mobile wp-lucky-wheel-shortcode-wheel-field "
                                   placeholder="<?php esc_attr_e( 'Please enter your mobile', 'wordpress-lucky-wheel' ) ?>">
                        </div>
						<?php
					}
					?>
					<?php
					//					$skip_email = apply_filters( 'wplwl_skip_enter_email', __return_false() );
					$email_field = $this->settings->get_params( 'custom_field_email_enable' );
					if ( $email_field === 'on' ) {

						?>
                        <div class=" wp-lucky-wheel-shortcode-wheel-field-email-wrap wp-lucky-wheel-shortcode-wheel-field-wrap ">
                        <span class=" wp-lucky-wheel-shortcode-wheel-field-error-email wp-lucky-wheel-shortcode-wheel-field-error "></span>
                            <input type="email"
                                   value="<?php echo esc_attr( is_user_logged_in() ? wp_get_current_user()->user_email : '' ) ?>"
                                   class=" wp-lucky-wheel-shortcode-wheel-field-email wp-lucky-wheel-shortcode-wheel-field "
                                   placeholder="<?php esc_attr_e( "Please enter your email", 'wordpress-lucky-wheel' ) ?>">
                        </div>
						<?php
					}
					?>
                    <!--captcha-->
                    <div class="wplwl_shortcode_recaptcha_wrap">
                        <div class="wplwl-shortcode-recaptcha-field"
                             style="<?php echo esc_attr($wplwl_recaptcha ? '' : 'display:none;'); ?>">
                            <div id="wplwl-shortcode-recaptcha" class="wplwl-shortcode-recaptcha"></div>
                            <input type="hidden" value="" id="wplwl-shortcode-g-validate-response">
                        </div>
                        <div id="wplwl_warring_shortcode_recaptcha"></div>
                    </div>
                    <div class="<?php echo esc_attr( $this->set( array( 'wheel-button-wrap' ) ) ) ?>">
                        <span class="<?php echo esc_attr( $this->set( array( 'wheel-button' ) ) ) ?>"><?php echo wp_kses_post( $this->settings->get_params( 'wheel_wrap', 'spin_button', $this->language ) ); ?></span>
                    </div>
					<?php
					if ( 'on' == $this->settings->get_params( 'wheel_wrap', 'gdpr' ) ) {
						$gdpr_message = $this->settings->get_params( 'wheel_wrap', 'gdpr_message', $this->language );
						if ( empty( $gdpr_message ) ) {
							$gdpr_message = esc_html__( 'I agree with the term and condition', 'wordpress-lucky-wheel' );
						}
						?>
                        <div class="<?php echo esc_attr( $this->set( array( 'wheel-gdpr-wrap' ) ) ) ?>">
                            <input type="checkbox">
                            <span><?php echo wp_kses_post( $gdpr_message ) ?></span>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
            <div class="<?php echo esc_attr( $this->set( 'result-container' ) ) ?>">
            </div>
        </div>

		<?php
		return ob_get_clean();
	}
}
