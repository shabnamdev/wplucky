<?php

/**
 * Class VI_WORDPRESS_LUCKY_WHEEL_Frontend_Frontend
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WORDPRESS_LUCKY_WHEEL_Frontend_Frontend {
	protected $settings;
	protected $pointer_position;
	protected $background_effect;
	protected $language;
	protected $new_data_updated = false;
	protected $email_table;

	public function __construct() {
		$this->settings    = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance();
		$this->language    = '';
		$this->email_table = WPLWL_EMAIL_Table::get_instance();

		add_action( 'wplwl_schedule_add_recipient_to_list', array( $this, 'add_recipient_to_list' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );
		if ( $this->settings->get_params( 'ajax_endpoint' ) === 'ajax' ) {
			add_action( 'wp_ajax_wplwl_get_email', array( $this, 'get_email' ) );
			add_action( 'wp_ajax_nopriv_wplwl_get_email', array( $this, 'get_email' ) );
		} else {
			add_action( 'rest_api_init', array( $this, 'register_api' ) );
		}
		if ( get_option( 'wpwlwl_updated_database' ) ) {
			$this->new_data_updated = true;
		}
	}

	public function get_from_address() {
		return sanitize_email( $this->settings->get_params( 'result', 'email' )['from_address']??'' );
	}

	public function get_from_name() {
		return wp_specialchars_decode( esc_html( $this->settings->get_params( 'result', 'email' )['from_name'] ??''), ENT_QUOTES );
	}

	public function get_content_type() {
		return 'text/html';
	}

	public function send_email( $user_email, $customer_name, $mobile = '', $value = '', $label = '', $language = '', $email_template = '' ) {
		$label        = str_replace( array( '/n', '\n' ), ' ', $label );
		$label        = str_replace( array(
			'{quantity_label}'
		), '', $label );
		$label        = preg_replace( '/ +/', ' ', $label );
		$date_format  = get_option( 'date_format', 'F d, Y' );
		$date         = new DateTime();
		$now          = $date->format( $date_format );
		$email_design = $this->settings->get_params( 'result', 'email' );
		$bg           = isset( $email_design['background_color'] ) ? $email_design['background_color'] : '';
		$body         = isset( $email_design['body_background_color'] ) ? $email_design['body_background_color'] : '';
		$base         = isset( $email_design['base_color'] ) ? $email_design['base_color'] : '';
		$text         = isset( $email_design['body_text_color'] ) ? $email_design['body_text_color'] : '';
		$img          = isset( $email_design['header_image'] ) ? $email_design['header_image'] : '';
		if ( sanitize_email( $email_design['from_address'] ) ) {
			add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		}
		if ( !empty($email_design['from_address']) ) {
			add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		}
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$header       = 'Content-Type: text/html; charset=utf-8;';
		$email_temp   = $this->settings->get_params( 'result', 'email', $language );
		$footer_text  = isset( $email_temp['footer_text'] ) ? $email_temp['footer_text'] : '';
		$use_template = VI_WORDPRESS_LUCKY_WHEEL_Plugins_9mail::send_mail($email_template,$user_email,[
			'search' => [
				'{wplwl_prize_label}',
				'{wplwl_customer_name}',
				'{wplwl_customer_mobile}',
				'{wplwl_prize_value}',
				'{wplwl_customer_email}',
				'{wplwl_site_title}',
			],
			'replace' => [$label,$customer_name,$mobile,strtoupper( $value ),$user_email,get_bloginfo( 'name' )],
        ]);
		if ( ! $use_template ) {
			$content       = stripslashes( nl2br( $email_temp['content'] ??'') );
			$content       = str_replace( '{prize_label}', $label, $content );
			$content       = str_replace( '{customer_name}', $customer_name, $content );
			$content       = str_replace( '{customer_mobile}', $mobile, $content );
			$content       = str_replace( '{prize_value}', $value, $content );
			$content       = str_replace( '{today}', $now, $content );
			$subject       = stripslashes( $email_temp['subject'] );
			$email_heading = isset( $email_temp['heading'] ) ? $email_temp['heading'] : '';

			$content = self::wrap_email_message( $bg, $img, $body, $base, $email_heading, $text, $content, $footer_text );
			wp_mail( $user_email, $subject, $content, $header );
		}
		$admin_email         = $this->settings->get_params( 'result', 'admin_email' );
		$admin_email_address = stripslashes( empty( $admin_email['to'] ) ? $email_design['from_address'] : $admin_email['to'] );
		if ( 'on' === ($admin_email['enable'] ??'') && $admin_email_address ) {

			$admin_mail_header[] = 'Content-Type:text/html; charset=utf-8;';
			$admin_mail_header[] = 'Cc:' . apply_filters( 'wplwl_admin_cc_result', '' );

			$content1       = stripslashes( nl2br( $admin_email['content']??'' ) );
			$content1       = str_replace( '{customer_email}', $user_email, $content1 );
			$content1       = str_replace( '{prize_label}', $label, $content1 );
			$content1       = str_replace( '{customer_name}', $customer_name, $content1 );
			$content1       = str_replace( '{customer_mobile}', $mobile, $content1 );
			$content1       = str_replace( '{prize_value}', $value, $content1 );
			$content1       = str_replace( '{today}', $now, $content1 );
			$subject1       = stripslashes( $admin_email['subject'] );
			$email_heading1 = isset( $admin_email['heading'] ) ? $admin_email['heading'] : '';
			$content1       = self::wrap_email_message( $bg, $img, $body, $base, $email_heading1, $text, $content1, $footer_text );
			wp_mail( $admin_email_address, $subject1, $content1, $admin_mail_header );
		}
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	public static function wrap_email_message( $bg, $img, $body, $base, $email_heading, $text, $content, $footer_text ) {
		ob_start();
		?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>"/>
            <title><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></title>
        </head>
        <body <?php echo esc_attr( is_rtl() ? 'rightmargin' : 'leftmargin' ); ?>="0" marginwidth="0" topmargin="0"
        marginheight=
        "0" offset="0">
        <div id="wrapper" dir="<?php echo esc_attr( is_rtl() ? 'rtl' : 'ltr' ); ?>"
             style="background-color: <?php echo esc_attr( $bg ); ?>;
                     margin: 0;
                     padding: 70px 0 70px 0;
                     -webkit-text-size-adjust: none !important;
                     width: 100%;">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tr>
                    <td align="center" valign="top">
                        <div id="template_header_image">
							<?php
							if ( $img ) {
								echo '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . esc_html( get_bloginfo( 'name', 'display' ) ) . '" /></p>';
							}
							?>
                        </div>
                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container"
                               style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important;
                                       background-color: <?php echo esc_attr( $body ); ?>;
                                       border-radius: 3px !important;">
                            <tr>
                                <td align="center" valign="top">
                                    <!-- Header -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600"
                                           id="template_header"
                                           style="background-color: <?php echo esc_attr( $base ); ?>;
                                                   border-radius: 3px 3px 0 0 !important;
                                                   border-bottom: 0;
                                                   font-weight: bold;
                                                   line-height: 100%;
                                                   vertical-align: middle;
                                                   font-family: Helvetica, Roboto, Arial, sans-serif;">
                                        <tr>
                                            <td id="header_wrapper" style="padding: 36px 48px;display: block;">
                                                <h1><?php echo wp_kses_post( $email_heading ); ?></h1>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Header -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- Body -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600"
                                           id="template_body">
                                        <tr>
                                            <td valign="top" id="body_content"
                                                style="background-color: <?php echo esc_attr( $body ); ?>;">
                                                <!-- Content -->
                                                <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td valign="top" style="padding: 48px;">
                                                            <div id="body_content_inner" style="
                                                                    font-family: Helvetica, Roboto, Arial, sans-serif;
                                                                    font-size: 14px;
                                                                    line-height: 150%;
                                                                    text-align: <?php echo esc_attr( is_rtl() ? 'right' : 'left' ); ?>;">
                                                                <div class="text"
                                                                     style="color: <?php echo esc_attr( $text ); ?>;
                                                                             font-family: Helvetica, Roboto, Arial, sans-serif;">
																	<?php
																	echo wp_kses_post( $content );
																	?>

                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <!-- Footer -->
                                    <table border="0" cellpadding="10" cellspacing="0" width="600"
                                           id="template_footer">
                                        <tr>
                                            <td valign="top">
                                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td colspan="2" valign="middle" id="credit" style="border:0;
                                                                font-family: Arial;
                                                                font-size:12px;
                                                                line-height:125%;
                                                                text-align:center;
                                                                padding: 0 48px 48px 48px;">
															<?php echo wp_kses_post( wpautop( wptexturize( $footer_text ) ) ); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Footer -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        </body>
        </html>
		<?php
		$content = ob_get_clean();
		if (VI_WORDPRESS_LUCKY_WHEEL_Plugins_9mail::$is_active || class_exists( 'EmTmplF\WP_Email_Templates_Designer' ) ){
			$content .='ignore_9mail';
		}
		return $content;
	}

	protected function send_email_no_prize_left( $params ) {
		$non = 0;
        if (!isset($params['wheel']['prize_type']) || !is_array($params['wheel']['prize_type'])){
	        $params['wheel']['prize_type']=[];
        }
		foreach ( $params['wheel']['prize_type'] as $key => $value ) {
			if ( $value === 'non' || $params['wheel']['prize_quantity'][ $key ] == 0 ) {
				$non ++;
			}
		}
		if ( $non === count( $params['wheel']['prize_type'] ) ) {
			$email_design        = $this->settings->get_params( 'result', 'email' );
			$admin_email_address = stripslashes( empty( $admin_email['to'] ) ? ($email_design['from_address']??'') : ($admin_email['to'] ??'') );
			if ( $admin_email_address ) {
				$email_temp = $this->settings->get_params( 'result', 'email' );
				$content    = sprintf( wp_kses_post( 'All prizes of WordPress Lucky Wheel have been won. Please go to <a target="_blank" href="%s">WordPress Lucky Wheel settings</a> to config the wheel.' ), admin_url( 'admin.php?page=wordpress-lucky-wheel#/wheel' ) );
				$header     = 'Content-Type: text/html; charset=utf-8;';

				$admin_mail_header[] = 'Content-Type:text/html; charset=utf-8;';
				$admin_mail_header[] = 'Cc:' . apply_filters( 'wplwl_admin_cc_no_left_price', '' );

				$subject       = 'WordPress Lucky Wheel alert';
				$email_heading = 'No prize left to spin';
				$bg            = isset( $email_design['background_color'] ) ? $email_design['background_color'] : '';
				$body          = isset( $email_design['body_background_color'] ) ? $email_design['body_background_color'] : '';
				$base          = isset( $email_design['base_color'] ) ? $email_design['base_color'] : '';
				$text          = isset( $email_design['body_text_color'] ) ? $email_design['body_text_color'] : '';
				$img           = isset( $email_design['header_image'] ) ? $email_design['header_image'] : '';
				$footer_text   = isset( $email_temp['footer_text'] ) ? $email_temp['footer_text'] : '';
				$content       = self::wrap_email_message( $bg, $img, $body, $base, $email_heading, $text, $content, $footer_text );
				wp_mail( $admin_email_address, $subject, $content, $admin_mail_header );
			}
		}
	}

	public function frontend_enqueue() {
		if ( $this->settings->get_params( 'general', 'enable' ) != 'on' ) {
			return;
		}
		if ( apply_filters( 'wplwl_disable_email_popup', false ) ) {
			return;
		}
		$show = true;
		if ( $this->settings->get_params( 'notify', 'show_only_front' ) == 'on' || $this->settings->get_params( 'notify', 'show_only_blog' ) == 'on' || $this->settings->get_params( 'notify', 'show_only_shop' ) == 'on' ) {
			$show = false;
			if ( is_front_page() && $this->settings->get_params( 'notify', 'show_only_front' ) == 'on' ) {
				$show = true;
			}
			if ( is_home() && $this->settings->get_params( 'notify', 'show_only_blog' ) == 'on' ) {
				$show = true;
			}
		}
		if ( ! $show ) {
			return;
		}
		$logic_value = $this->settings->get_params( 'notify', 'conditional_tags' );
		if ( $logic_value ) {
			if ( stristr( $logic_value, "return" ) === false ) {
				$logic_value = "return (" . $logic_value . ");";
			}
			if ( ! eval( $logic_value ) ) {//phpcs:ignore Generic.PHP.ForbiddenFunctions.Found
				return;
			}
		}
		if ( isset( $_COOKIE['wplwl_cookie'] ) ) {
			return;
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
		$wheel          = $this->settings->get_params( 'wheel' );
        if (!is_array($wheel)){
            $wheel = [];
        }
        if (!isset($wheel['prize_type']) || !is_array($wheel['prize_type'])){
            $wheel['prize_type'] = [];
        }
        if (!isset($wheel['probability']) || !is_array($wheel['probability'])){
            $wheel['probability'] = [];
        }
		$coupon_count   = count( $wheel['prize_type'] );
		$prize_quantity = $this->settings->get_params( 'wheel', 'prize_quantity' );
        if (!is_array($prize_quantity)){
            $prize_quantity = [];
        }
		$custom_label   = $this->settings->get_params( 'wheel', 'custom_label', $this->language );
		$quantity_label = $this->settings->get_params( 'wheel', 'quantity_label', $this->language );
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
				$probability += absint( $wheel['probability'][ $count ]??0 );
			} else {
				if ( $prize_quantity[ $count ] != 0 ) {
					$probability += absint( $wheel['probability'][ $count ] ??0);
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
			return;
		}
        $mobile_enable =  $this->settings->get_params( 'general', 'mobile' ) === 'on';
		$this->settings::enqueue_script(
			array( 'wordpress-lucky-wheel-frontend' ),
			array( 'wordpress-lucky-wheel' ),
			array( 0 ),
		);
        $font = '';
		if ( $this->settings->get_params( 'wheel_wrap', 'font' ) ) {
			$font = $this->settings->get_params( 'wheel_wrap', 'font' );
			wp_enqueue_style( 'wordpress-lucky-wheel-google-font-' . strtolower( str_replace( '+', '-', $font ) ), '//fonts.googleapis.com/css?family=' . $font . ':300,400,700', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
			$font = str_replace( '+', ' ', $font );
		}
		$font_wheel = apply_filters( 'wpml_font_text_wheel', '' );
		if ( ! empty( $font_wheel ) ) {
			wp_enqueue_style( 'wplwl-wheel-google-font-' . strtolower( str_replace( '+', '-', $font_wheel ) ), '//fonts.googleapis.com/css?family=' . $font_wheel . ':300,400,700', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
		}

		if ( $this->settings->get_params( 'wheel_wrap', 'congratulations_effect' ) == 'firework' ) {
			$this->settings::enqueue_style(
				array( 'wordpress-lucky-wheel-frontend-style-firework', ),
				array( 'firework' ),
				array()
			);
		}
		$this->background_effect = $this->settings->get_params( 'wheel_wrap', 'background_effect' );
		if ( $this->background_effect == 'random' ) {
			$randoms                 = array_keys( VI_WORDPRESS_LUCKY_WHEEL_DATA::get_all_bg_effects() );
			$rand_index              = wp_rand( 0, count( $randoms ) - 2 );
			$this->background_effect = $randoms[ $rand_index ];
		}
		$background_effect_css='';
		switch ( $this->background_effect ) {
			case 'snowflakes':
				$background_effect_css='snowflakes';
				break;
			case 'snowflakes-1':
				$background_effect_css='snowflakes-1';
				break;
			case 'snowflakes-2-1':
			case 'snowflakes-2-2':
			case 'snowflakes-2-3':
                $background_effect_css='snowflakes-2';
				break;
			default:
		}
        if ($background_effect_css){
	        $this->settings::enqueue_style(
		        array( 'wordpress-lucky-wheel-frontend-style-snowflakes', ),
		        array($background_effect_css ),
		        array()
	        );
        }
		$this->settings::enqueue_style(
			array( 'wordpress-lucky-wheel-gift-icons-style','wordpress-lucky-wheel-frontend-style' ),
			array('giftbox','wordpress-lucky-wheel' ),
			array()
		);
		$inline_css = '.wplwl_lucky_wheel_content {';
		if ( $this->settings->get_params( 'wheel_wrap', 'bg_image' ) ) {
			$bg_image_url = wp_get_attachment_url( $this->settings->get_params( 'wheel_wrap', 'bg_image' ) );
			if ( empty( $bg_image_url ) ) {
				$bg_image_url = $this->settings->get_params( 'wheel_wrap', 'bg_image' );
			}
			$inline_css .= 'background-image:url("' . $bg_image_url . '");background-repeat: no-repeat;background-size:cover;background-position:center;';
		}
		if ( $this->settings->get_params( 'wheel_wrap', 'bg_color' ) ) {
			$inline_css .= 'background-color:' . $this->settings->get_params( 'wheel_wrap', 'bg_color' ) . ';';
		}
		if ( $this->settings->get_params( 'wheel_wrap', 'text_color' ) ) {
			$inline_css .= 'color:' . $this->settings->get_params( 'wheel_wrap', 'text_color' ) . ';';
		}
		$inline_css .= '}';
		if ( 'on' === ($wheel['show_full_wheel']??'') ) {
			$inline_css .= '.wplwl_lucky_wheel_content .wheel_content_left{margin-left:0 !important;}';
			$inline_css .= '.wplwl_lucky_wheel_content .wheel_content_right{width:48% !important;}';
		}
		$inline_css .= '.wplwl_wheel_icon{';
		switch ( $this->settings->get_params( 'notify', 'position' ) ) {
			case 'top-left':
				$inline_css .= 'top:15px;left:0;margin-left: -100%;';
				break;
			case 'top-right':
				$inline_css .= 'top:15px;right:0;margin-right: -100%;';
				break;
			case 'bottom-left':
				$inline_css .= 'bottom:5px;left:5px;margin-left: -100%;';
				break;
			case 'bottom-right':
				$inline_css .= 'bottom:5px;right:5px;margin-right: -100%;';
				break;
			case 'middle-left':
				$inline_css .= 'bottom:45%;left:0;margin-left: -100%;';
				break;
			case 'middle-right':
				$inline_css .= 'bottom:45%;right:0;margin-right: -100%;';
				break;
		}
		$inline_css .= '}';

		if ( $this->settings->get_params( 'wheel_wrap', 'pointer_color' ) ) {
			$inline_css .= '.wplwl_pointer:before{color:' . $this->settings->get_params( 'wheel_wrap', 'pointer_color' ) . ';}';
		}
		//wheel wrap design
		$inline_css .= '.wheel_content_right>.wplwl_user_lucky>.wplwl_spin_button{';
		if ( $this->settings->get_params( 'wheel_wrap', 'spin_button_color' ) ) {
			$inline_css .= 'color:' . $this->settings->get_params( 'wheel_wrap', 'spin_button_color' ) . ';';
		}

		if ( $this->settings->get_params( 'wheel_wrap', 'spin_button_bg_color' ) ) {
			$inline_css .= 'background-color:' . $this->settings->get_params( 'wheel_wrap', 'spin_button_bg_color' ) . ';';
		}
		$inline_css .= '}';
		if ( $font ) {
			$inline_css .= '.wplwl_lucky_wheel_content .wheel-content-wrapper .wheel_content_right,.wplwl_lucky_wheel_content .wheel-content-wrapper .wheel_content_right input,.wplwl_lucky_wheel_content .wheel-content-wrapper .wheel_content_right span,.wplwl_lucky_wheel_content .wheel-content-wrapper .wheel_content_right a,.wplwl_lucky_wheel_content .wheel-content-wrapper .wheel_content_right .wplwl-frontend-result{font-family:' . $font . ' !important;}';
		}
		$popup_icon = $this->settings->get_params( 'notify', 'popup_icon' );
		if ( $popup_icon ) {
			$popup_icon_class    = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_gift_icon_class( $popup_icon );
			$popup_icon_color    = $this->settings->get_params( 'notify', 'popup_icon_color' );
			$popup_icon_bg_color = $this->settings->get_params( 'notify', 'popup_icon_bg_color' );
			$inline_css          .= ".wplwl_wheel_icon.{$popup_icon_class}{padding:6px;border-radius:5px;}";
			if ( $popup_icon_color ) {
				$inline_css .= ".wplwl_wheel_icon.{$popup_icon_class}{color:{$popup_icon_color};}";
			}
			if ( $popup_icon_bg_color ) {
				$inline_css .= ".wplwl_wheel_icon.{$popup_icon_class}{background-color:{$popup_icon_bg_color};}";
			}
		}
		$inline_css .= $this->settings->get_params( 'wheel_wrap', 'custom_css' );
		wp_add_inline_style( 'wordpress-lucky-wheel-frontend-style', $inline_css );
		$time_if_close = $this->settings->get_params( 'notify', 'time_on_close' );
		if ( $this->settings->get_params( 'notify', 'time_on_close_unit' ) && ! empty( $time_if_close ) ) {
			switch ( $this->settings->get_params( 'notify', 'time_on_close_unit' ) ) {
				case 'm':
					$time_if_close *= 60;
					break;
				case 'h':
					$time_if_close *= 3600;
					break;
				case 'd':
					$time_if_close *= 86400;
					break;
				default:
			}
		}
		$intent = $this->settings->get_params( 'notify', 'intent' );
		if ( $intent == 'random' ) {
			$ran = wp_rand( 1, 4 );
			switch ( $ran ) {
				case 1:
					$intent = 'popup_icon';
					break;
				case 2:
					$intent = 'show_wheel';
					break;
				case 3:
					$intent = 'on_scroll';
					break;
				case 4:
					$intent = 'on_exit';
					break;
			}
		}
		$limit_time_warning = esc_html__( 'You have to wait until your next spin.', 'wordpress-lucky-wheel' );
		switch ( $this->settings->get_params( 'notify', 'show_again_unit' ) ) {
			case 's':
				$limit_time_warning = sprintf( esc_html( 'You can only spin 1 time every %s seconds' ), esc_html( $this->settings->get_params( 'notify', 'show_again' ) ) );
				break;
			case 'm':
				$limit_time_warning = sprintf( esc_html( 'You can only spin 1 time every %s minutes' ), esc_html( $this->settings->get_params( 'notify', 'show_again' ) ) );
				break;
			case 'h':
				$limit_time_warning = sprintf( esc_html( 'You can only spin 1 time every %s hours' ), esc_html( $this->settings->get_params( 'notify', 'show_again' ) ) );
				break;
			case 'd':
				$limit_time_warning = sprintf( esc_html( 'You can only spin 1 time every %s days' ), esc_html( $this->settings->get_params( 'notify', 'show_again' ) ) );
				break;

		}
		$this->pointer_position = $this->settings->get_params( 'wheel_wrap', 'pointer_position' );
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
		wp_localize_script( 'wordpress-lucky-wheel-frontend', '_wplwl_get_email_params', array(
			'ajaxurl'            => $this->settings->get_params( 'ajax_endpoint' ) == 'ajax' ? ( admin_url( 'admin-ajax.php?action=wplwl_get_email' ) ) : site_url() . '/wp-json/wordpress_lucky_wheel/spin',
			'pointer_position'   => $this->pointer_position,
			'wheel_dot_color'    => $this->settings->get_params( 'wheel_wrap', 'wheel_dot_color' ),
			'wheel_border_color' => $this->settings->get_params( 'wheel_wrap', 'wheel_border_color' ),
			'wheel_center_color' => $this->settings->get_params( 'wheel_wrap', 'wheel_center_color' ),
			'gdpr'               => $this->settings->get_params( 'wheel_wrap', 'gdpr' ) === 'on' ? 1 : '',
			'gdpr_warning'       => esc_html__( 'Please agree with our term and condition.', 'wordpress-lucky-wheel' ),

			'position'        => $this->settings->get_params( 'notify', 'position' ),
			'show_again'      => $this->settings->get_params( 'notify', 'show_again' ),
			'scroll_amount'   => $this->settings->get_params( 'notify', 'scroll_amount' ),
			'show_again_unit' => $this->settings->get_params( 'notify', 'show_again_unit' ),
			'intent'          => $intent,
			'hide_popup'      => $this->settings->get_params( 'notify', 'hide_popup' ) ==='on',

			'slice_text_color'                  => ( isset( $wheel['slice_text_color'] ) && $wheel['slice_text_color'] ) ? $wheel['slice_text_color'] : '#fff',
			'bg_color_random'                          => ($wheel['random_color']??'') === 'on' ? 1: '',
			'bg_color'                          => ($wheel['random_color']??'') === 'on' ? $this->settings::get_random_color() : $wheel['bg_color'],
			'slices_text_color'                 => ($wheel['slices_text_color']??''),
			'label'                             => $wheel['label'],
			'prize_type'                        => $wheel['prize_type'],
			'spinning_time'                     => $wheel['spinning_time']??1,
			'wheel_speed'                       => $wheel['wheel_speed']??3,
			'auto_close'                        => $this->settings->get_params( 'result', 'auto_close' ),
			'show_wheel'                        => wplwl_get_explode( $this->settings->get_params( 'notify', 'show_wheel' ), ',' ),
			'time_if_close'                     => $time_if_close,
			'empty_email_warning'               => esc_html__( '*Please enter your email', 'wordpress-lucky-wheel' ),
			'wplwl_warring_recaptcha'           => esc_html__( '*Require reCAPTCHA verification', 'wordpress-lucky-wheel' ),
			'invalid_email_warning'             => esc_html__( '*Please enter a valid email address', 'wordpress-lucky-wheel' ),
			'limit_time_warning'                => $limit_time_warning,
			'custom_field_name_enable'          => $this->settings->get_params( 'custom_field_name_enable' ) === 'on' ? 1 : '',
			'custom_field_name_enable_mobile'   => $this->settings->get_params( 'custom_field_name_enable_mobile' ) === 'on' ? 1 : '',
			'custom_field_name_required'        => $this->settings->get_params( 'custom_field_name_required' ) === 'on' ? 1 : '',
			'custom_field_name_message'         => esc_html__( 'Name is required!', 'wordpress-lucky-wheel' ),
			'custom_field_mobile_enable'        => $this->settings->get_params( 'custom_field_mobile_enable' )=== 'on' ? 1 : '',
			'custom_field_mobile_enable_mobile' => $this->settings->get_params( 'custom_field_mobile_enable_mobile' )=== 'on' ? 1 : '',
			'custom_field_mobile_required'      => $this->settings->get_params( 'custom_field_mobile_required' ),
			'custom_field_mobile_message'       => esc_html__( 'Mobile is required!', 'wordpress-lucky-wheel' ),
			'custom_field_mobile_warning'       => esc_html__( 'Please enter a valid mobile', 'wordpress-lucky-wheel' ),
			'show_full_wheel'                   => $wheel['show_full_wheel']??'',
			'font_size'                         => $wheel['font_size']??100,
			'wheel_size'                        => $wheel['wheel_size']??100,
			'congratulations_effect'            => $this->settings->get_params( 'wheel_wrap', 'congratulations_effect' ),
			'images_dir'                        => VI_WORDPRESS_LUCKY_WHEEL_IMAGES,
			'language'                          => $this->language,
			'rotate'                            => in_array( $this->background_effect, array(
				'leaf-1',
				'leaf-2',
			) ),
			'font_text_wheel'                   => apply_filters( 'wpml_font_text_wheel', '' ),
			'wplwl_recaptcha_site_key'          => $this->settings->get_params( 'wplwl_recaptcha_site_key' ),
			'wplwl_recaptcha_version'           => $this->settings->get_params( 'wplwl_recaptcha_version' ),
			'wplwl_recaptcha_secret_theme'      => $this->settings->get_params( 'wplwl_recaptcha_secret_theme' ),
			'wplwl_recaptcha'                   => $this->settings->get_params( 'wplwl_recaptcha' ),
			'wplwl_skip_enter_email'            => $this->settings->get_params( 'custom_field_email_enable' ) ? '' : 1,
			'wplwl_mobile_enable'            => $mobile_enable
		) );
		add_action( 'wp_footer', array( $this, 'draw_wheel' ) );
		if ( $this->settings->get_params( 'wplwl_recaptcha' ) ) {
			if ( $this->settings->get_params( 'wplwl_recaptcha_version' ) == 2 ) {
				?>
                <script src='https://www.google.com/recaptcha/api.js?hl=<?php echo esc_attr( $this->language ? $this->language : get_locale() ) ?>&render=explicit'
                        async
                        defer></script>
				<?php
			} elseif ( $this->settings->get_params( 'wplwl_recaptcha_site_key' ) ) {
				?>
                <script src="https://www.google.com/recaptcha/api.js?hl=<?php echo esc_attr( $this->language ? $this->language : get_locale() ) ?>&render=<?php echo esc_attr( $this->settings->get_params( 'wplwl_recaptcha_site_key' ) ); ?>"></script>
				<?php
			}
		}

	}

	/**
	 * Register API json
	 */
	public function register_api() {
		register_rest_route(
			'wordpress_lucky_wheel', '/spin', array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_email' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function add_recipient_to_list( $email, $list_id ) {
		$sendgrid = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendgrid();
		$sendgrid->add_recipient_to_list( $email, $list_id );
	}

	public function draw_wheel() {
		/*
		 * since version 1.2.0
		 * Check new custom table
		 */
		if ( ! $this->new_data_updated ) {
			return;
		}
		if ( isset( $_COOKIE['wplwl_cookie'] ) ) {
			return;
		}
		$spin_button = $this->settings->get_params( 'wheel_wrap', 'spin_button', $this->language );
		if ( empty( $spin_button ) ) {
			$spin_button = esc_html__( 'Try Your Lucky', 'wordpress-lucky-wheel' );
		}
        echo '<div class="wplwl_lucky_wheel_wrap">';
		wp_nonce_field( 'wordpress_lucky_wheel_nonce_action', '_wordpress_lucky_wheel_nonce' );
		$center_image    = wp_get_attachment_url( $this->settings->get_params( 'wheel_wrap', 'wheel_center_image' ) );
		$wplwl_recaptcha = $this->settings->get_params( 'wplwl_recaptcha' );
		$custom_field_mobile_enable = $this->settings->get_params( 'custom_field_mobile_enable' ) === 'on';
        if ($custom_field_mobile_enable) {
	        $country_codes = $this->settings->get_params( 'custom_field_mobile_phone_countries' );
	        if ( ! is_array( $country_codes ) ) {
		        $country_codes = [];
	        }
	        if ( in_array( 0, $country_codes ) ) {
		        $country_codes = false;
	        }
	        if ( is_array( $country_codes ) ) {
		        $phone_countries = villatheme_json_decode( $this->settings::def_phone_country() );
		        ksort( $phone_countries );
		        if ( empty( $country_codes ) ) {
                    foreach ($phone_countries as $v){
                        if (is_array($v)){
                            $country_codes += $v;
                        }else{
                            $country_codes[] = $v;
                        }
                    }
		        }
		        $locate         = class_exists( 'WC_Geolocation' ) ? WC_Geolocation::geolocate_ip() : $this->settings::geolocate_ip();
		        $detect_phone_country = '';
                if (!empty($locate['country']) && isset($phone_countries[$locate['country']])){
                    $tmp = $phone_countries[$locate['country']];
                    if (is_array($tmp)){
                        $detect_phone_country = $tmp[0];
                    }else{
                        $detect_phone_country = $tmp;
                    }
                }
                $tmp = [];
                foreach ($country_codes as $v){
                    if (!$v){
                        continue;
                    }
                    foreach ($phone_countries as $j => $k){
	                    $check_phone_country = '';
                        if ($k === $v){
	                        $check_phone_country = true;
                        }elseif (is_array($k)){
                            foreach ($k as $k1){
	                            if ($k1 === $v){
		                            $check_phone_country = true;
                                    break;
	                            }
                            }
                        }
                        if ($check_phone_country){
                            $tmp[$v] =  "{$j}( {$v} )";
                            break;
                        }
                    }
                }
                $country_codes = $tmp;
	        }
        }
		if ( in_array( $this->background_effect, array(
			'snowflakes-2-1',
			'snowflakes-2-2',
			'snowflakes-2-3'
		) ) ) {
			?>
            <div class="wplwl-overlay wplwl-background-effect-snowflakes-2 <?php echo 'wplwl-background-effect-' . esc_attr( $this->background_effect ); ?>">
                <i></i>
            </div>
			<?php
		} else {
			?>
            <div class="wplwl-overlay"></div>
			<?php
		}
		?>
        <input id="wplwl_center_image" type="hidden" value="<?php echo esc_url( $center_image ) ?>">
		<?php
		$class = array( 'wplwl_lucky_wheel_content' );// lucky_wheel_content_tablet , wplwl_lucky_wheel_content_mobile
		if ( $this->pointer_position === 'top' ) {
			$class[] = 'wplwl_margin_position';
			$class[] = 'wplwl_spin_top';
		} elseif ( $this->pointer_position === 'right' ) {
			$class[] = 'wplwl_margin_position';
		} elseif ( $this->pointer_position === 'bottom' ) {
			$class[] = 'wplwl_margin_position';
			$class[] = 'wplwl_spin_bottom';
		}
		if ( in_array( $this->background_effect, array(
			'snowflakes-2-1',
			'snowflakes-2-2',
			'snowflakes-2-3'
		) ) ) {
			$class[] = 'wplwl-background-effect-snowflakes-2';
			$class[] = 'wplwl-background-effect-' . $this->background_effect;
		} elseif ( in_array( $this->background_effect, array(
			'hearts',
			'heart',
			'smile',
			'star',
			'leaf-1',
			'leaf-2',
			'halloween-1',
			'halloween-2',
			'halloween-3'
		) ) ) {
			$class[] = "wplwl-background-effect-falling-leaves";
			$class[] = "wplwl-background-effect-{$this->background_effect}";
		}
		?>
        <div class="<?php echo esc_attr( implode( ' ', $class ) ) ?>">
			<?php
			switch ( $this->background_effect ) {
				case 'snowflakes':
					self::snowflake_html();
					break;
				case 'snowflakes-1':
					self::snowflake_1_html();
					break;
				case 'snowflakes-2-1':
				case 'snowflakes-2-2':
				case 'snowflakes-2-3':
					?>
                    <i></i>
					<?php
					break;
				default:
			}
			?>
            <div class="wheel-content-wrapper">
                <div class="wheel_content_left">
                    <div class="wplwl-frontend-result"></div>
                    <div class="wplwl_wheel_spin">
                        <canvas id="wplwl_canvas">
                        </canvas>
                        <canvas id="wplwl_canvas1" class="<?php
						if ( $this->pointer_position == 'top' ) {
							echo 'canvas_spin_top';
						} elseif ( $this->pointer_position == 'bottom' ) {
							echo 'canvas_spin_bottom';
						} ?>">
                        </canvas>
                        <canvas id="wplwl_canvas2">
                        </canvas>
                        <div class="wplwl_wheel_spin_container">
                            <div class="wplwl_pointer_before"></div>
                            <div class="wplwl_pointer_content">
                                <span class="wplwl-location wplwl_pointer <?php
                                if ( $this->pointer_position == 'top' ) {
	                                echo 'pointer_spin_top';
                                } elseif ( $this->pointer_position == 'bottom' ) {
	                                echo 'pointer_spin_bottom';
                                } ?>"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wheel_content_right">
                    <div class="wheel_description">
						<?php echo do_shortcode( $this->settings->get_params( 'wheel_wrap', 'description', $this->language ) ); ?>
                    </div>
                    <div class="wplwl-congratulations-effect">
                        <div class="wplwl-congratulations-effect-before"></div>
                        <div class="wplwl-congratulations-effect-after"></div>
                    </div>
                    <div class="wplwl_user_lucky">
						<?php
						if ( 'on' == $this->settings->get_params( 'custom_field_name_enable' ) ) {
							?>
                            <div class="wplwl_field_name_wrap">
                                <span id="wplwl_error_name"></span>
                                <input type="text" class="wplwl_field_input wplwl_field_name"
                                       name="wplwl_player_name"
                                       placeholder="<?php esc_html_e( 'Please enter your name', 'wordpress-lucky-wheel' ) ?>"
                                       id="wplwl_player_name">
                            </div>
							<?php
						}
						if ( $custom_field_mobile_enable) {
							?>
                            <div class="wplwl_field_mobile_wrap <?php echo !empty($country_codes) ? 'wplwl_field_mobile_wrap_country_code' : ''; ?>">
                                <span id="wplwl_error_mobile"></span>
								<?php

								if (!empty($country_codes)){
									echo wp_kses('<select name="wplwl_country_code" id="wplwl_country_code" class="wplwl_field_select">', $this->settings::filter_allowed_html());
									foreach ($country_codes as $k => $v){
										echo wp_kses(sprintf('<option value="%s" %s > %s </option>', $k, selected($k, $detect_phone_country), $v), $this->settings::filter_allowed_html());
									}
									echo wp_kses('</select>', $this->settings::filter_allowed_html());
								} ?>
                                <input type="tel" class="wplwl_field_input wplwl_field_mobile"
                                       name="wplwl_player_mobile"
                                       placeholder="<?php esc_html_e( 'Please enter your mobile', 'wordpress-lucky-wheel' ) ?>"
                                       id="wplwl_player_mobile">
                            </div>
							<?php
						}
						?>
						<?php
						//							$skip_email = apply_filters( 'wplwl_skip_enter_email', __return_false() );
						$email_field = $this->settings->get_params( 'custom_field_email_enable' );
						if ( $email_field === 'on' ) {
							?>
                            <div class="wplwl_field_email_wrap">
                                <span id="wplwl_error_mail"></span>
                                <input type="email" class="wplwl_field_input wplwl_field_email" name="wplwl_player_mail"
                                       value="<?php echo esc_attr( is_user_logged_in() ? wp_get_current_user()->user_email : '' ) ?>"
                                       placeholder="<?php esc_html_e( 'Please enter your email', 'wordpress-lucky-wheel' ) ?>"
                                       id="wplwl_player_mail">
                            </div>
							<?php
						}
						?>
                        <!--captcha-->
						<?php
						if ( $wplwl_recaptcha){
							?>
                            <div class="wplwl_recaptcha_wrap">
                                <div class="wplwl-recaptcha-field">
                                    <div id="wplwl-recaptcha" class="wplwl-recaptcha"></div>
                                    <input type="hidden" value="" id="wplwl-g-validate-response">
                                </div>
                                <div id="wplwl_warring_recaptcha"></div>
                            </div>
							<?php
						}
						?>
                        <span class="wplwl_chek_mail wplwl_spin_button button-primary"
                              id="wplwl_chek_mail"><?php echo wp_kses_post( $spin_button ) ?></span>
						<?php
						if ( 'on' == $this->settings->get_params( 'wheel_wrap', 'gdpr' ) ) {
							$gdpr_message = $this->settings->get_params( 'wheel_wrap', 'gdpr_message', $this->language );
							if ( empty( $gdpr_message ) ) {
								$gdpr_message = esc_html__( 'I agree with the term and condition', 'wordpress-lucky-wheel' );
							}
							?>
                            <div class="wplwl-gdpr-checkbox-wrap">
                                <input type="checkbox">
                                <span><?php echo wp_kses_post( $gdpr_message ) ?></span>
                            </div>
							<?php
						}
						if ( 'on' === $this->settings->get_params( 'wheel_wrap', 'close_option' ) ) {
							?>
                            <div class="wplwl-show-again-option">
                                <div class="wplwl-never-again">
                                    <span><?php esc_html_e( 'Never', 'wordpress-lucky-wheel' ); ?></span>
                                </div>
                                <div class="wplwl-reminder-later">
                                    <span class="wplwl-reminder-later-a"><?php esc_html_e( 'Remind later', 'wordpress-lucky-wheel' ); ?></span>
                                </div>
                                <div class="wplwl-close">
                                    <span><?php esc_html_e( 'No thanks', 'wordpress-lucky-wheel' ); ?></span>
                                </div>
                            </div>
							<?php
						}
						?>
                    </div>
                </div>
				<?php
				if ( $this->background_effect === 'floating-bubbles' ) {
					self::bubbles_html( array(
						'balloon-1',
						'balloon-2',
						'balloon-3',
						'balloon-4',
						'balloon-5',
						'balloon-2',
						'balloon-5',
						'balloon-1',
						'balloon-4',
						'balloon-3',
						'balloon-2',
						'balloon-5',
						'balloon-1',
						'balloon-4',
						'balloon-3',
						'balloon-1'
					) );
				} elseif ( $this->background_effect === 'floating-halloween' ) {
					self::bubbles_html( array(
						'pumpkin',
						'halloween-ghost',
						'pumpkin',
						'ghost',
						'pumpkin',
						'creepy-ghost',
						'pumpkin',
						'halloween-ghost',
						'pumpkin',
						'ghost',
						'pumpkin',
						'creepy-ghost',
						'pumpkin',
						'halloween-ghost',
						'pumpkin',
						'ghost',
						'pumpkin',
						'creepy-ghost',
					) );
				}
				?>
            </div>
            <div class="wplwl-close-wheel"><span class="wplwl-cancel"></span></div>
            <div class="wplwl-hide-after-spin">
                <span class="wplwl-cancel"></span>
            </div>
        </div>
		<?php
		$wheel_icon_class = 'wplwl_wheel_icon wordpress-lucky-wheel-popup-icon wplwl-wheel-position-' . $this->settings->get_params( 'notify', 'position' );
		$popup_icon       = $this->settings->get_params( 'notify', 'popup_icon' );
		if ( $popup_icon ) {
			$wheel_icon_class .= ' ' . VI_WORDPRESS_LUCKY_WHEEL_DATA::get_gift_icon_class( $popup_icon );
			?>
            <span class="<?php echo esc_attr( $wheel_icon_class ) ?>"></span>
			<?php
		} else {
			?>
            <canvas id="wplwl_popup_canvas" class="<?php echo esc_attr( $wheel_icon_class ) ?>" width="64"
                    height="64"></canvas>
			<?php
		}
        echo '</div>';
	}

	public function get_email() {
		if ( $this->settings->get_params( 'ajax_endpoint' ) === 'rest_api' ) {
			header( "Access-Control-Allow-Origin: *" );
			header( 'Access-Control-Allow-Methods: POST' );
		}

		/*
		 * Allow user spin not need enter email
		 * */
		$redirect_after_spin = apply_filters( 'wplwl_redirect_after_spin', __return_false() );
		$url_redirect_win    = apply_filters( 'wplwl_redirect_after_win', '' );
		$url_redirect_lost   = apply_filters( 'wplwl_redirect_after_lost', '' );
//		$skip_email = apply_filters( 'wplwl_skip_enter_email', __return_false() );
		$email_field = $this->settings->get_params( 'custom_field_email_enable' );
		if ( $email_field !== 'on' ) {
			$language            = isset( $_POST['language'] ) ? sanitize_text_field( $_POST['language'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$result              = 'lost';
			$result_notification = $this->settings->get_params( 'result', 'notification', $language )['lost'];
			$date_format         = get_option( 'date_format', 'F d, Y' );
			$date                = new DateTime();
			$today               = $date->format( $date_format );
			$wheel               = $this->settings->get_params( 'wheel' );
			$custom_label        = $this->settings->get_params( 'wheel', 'custom_label', $language );
			$allow               = 'yes';

			$url_redirect_after_spin = $url_redirect_lost;

			$stop          = self::get_result( $wheel );
			$email_coupons = array();
			$email_labels  = array();
			$wheel_label   = $custom_label[ $stop ];
            if (!isset($wheel['prize_type']) || !is_array($wheel['prize_type'])){
                $wheel['prize_type'] = [];
            }

			if ( ($wheel['prize_type'][ $stop ] ??'') != 'non' ) {
				$result                  = 'win';
				$url_redirect_after_spin = $url_redirect_win;
				$result_notification     = $this->settings->get_params( 'result', 'notification', $language )['win']??'';

				$code                = isset($wheel['custom_value'][ $stop ])? $wheel['custom_value'][ $stop ]: '';
				$email_coupons[]     = $code;
				$email_labels[]      = $wheel_label;
				$result_notification = str_replace( '{prize_value}', '<strong>' . $code . '</strong>', $result_notification );
				$result_notification = str_replace( '{prize_label}', '<strong>' . $wheel_label . '</strong>', $result_notification );
				$result_notification = str_replace( '{today}', '<strong>' . $today . '</strong>', $result_notification );
				$result_notification = str_replace( '{quantity_label}', '', $result_notification );
				$result_notification = str_replace( array( '\n', '/n' ), ' ', $result_notification );
				if ( isset( $wheel['prize_quantity'] ) ) {
					$prize_quantity_left = intval( $wheel['prize_quantity'][ $stop ]??0 );
					if ( $prize_quantity_left > 0 ) {
						$params                                     = $this->settings->get_params();
						$params['wheel']['prize_quantity'][ $stop ] = $prize_quantity_left - 1;
						update_option( '_wplwl_settings', $params );
					}
				}
			}
			$data = array(
				'allow_spin'          => $allow,
				'stop_position'       => $stop,
				'result_notification' => do_shortcode( $result_notification ),
				'result'              => $result,
			);
			if ( $redirect_after_spin ) {
				$data['url_redirect_after_spin'] = $url_redirect_after_spin;
			}
			wp_send_json( $data );
		}

		$g_validate_response = isset( $_POST['g_validate_response'] ) ? sanitize_text_field( $_POST['g_validate_response'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $g_validate_response && $this->settings->get_params( 'wplwl_recaptcha' ) ) {
			$msg            = array(
				'status'              => '',
				'message'             => '',
				'warning'             => '',
				'g_validate_response' => '1',
			);
			$msg['status']  = 'invalid';
			$msg['warning'] = esc_html__( '*No g_validate_response', 'wordpress-lucky-wheel' );
			wp_send_json( $msg );
			die;
		}
		if ( $g_validate_response && $this->settings->get_params( 'wplwl_recaptcha' ) ) {
			$msg = array(
				'status'              => '',
				'message'             => '',
				'warning'             => '',
				'g_validate_response' => '1',
			);
			if ( ! $g_validate_response ) {
				$msg['status']  = 'invalid';
				$msg['warning'] = esc_html__( '*Invalid google reCAPTCHA!', 'wordpress-lucky-wheel' );
				wp_send_json( $msg );
				die;
			}
			$wplwl_recaptcha_secret_key = $this->settings->get_params( 'wplwl_recaptcha_secret_key' );
			if ( ! $wplwl_recaptcha_secret_key ) {
				$msg['status']  = 'invalid';
				$msg['warning'] = esc_html__( '*Invalid google reCAPTCHA secret key!', 'wordpress-lucky-wheel' );
				wp_send_json( $msg );
				die;
			}
			$url  = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $wplwl_recaptcha_secret_key . '&response=' . $g_validate_response;
			$curl = curl_init();//phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
			curl_setopt_array( $curl, array(//phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => "",
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "POST",
				CURLOPT_POSTFIELDS     => '{}',
				CURLOPT_HTTPHEADER     => array(
					"content-type: application/json"
				),
			) );

			$response = curl_exec( $curl );//phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
			$err      = curl_error( $curl );//phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_error
			curl_close( $curl );//phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
			if ( $err ) {
				$msg['status']  = 'invalid';
				$msg['warning'] = "*reCAPTCHA cURL Error #:" . esc_html( $err );
				wp_send_json( $msg );
				die;
			} else {
				$data = json_decode( $response, true );
				if ( $this->settings->get_params( 'wplwl_recaptcha_version' ) == 2 ) {
					if ( ! $data['success'] ) {
						$msg['status']  = 'invalid';
						$msg['warning'] = esc_html__( '*reCAPTCHA verification failed', 'wordpress-lucky-wheel' );
						$msg['message'] = $data;
						wp_send_json( $msg );
						die();
					}
				} else {
					$g_score = isset( $data['score'] ) ? $data['score'] : 0;
					if ( $g_score < 0.5 ) {
						$msg['status']  = 'invalid';
						$msg['warning'] = esc_html( '*reCAPTCHA score ' . $g_score . ' lower than threshold 0.5 ' );
						$msg['message'] = $data;
						wp_send_json( $msg );
						die();
					}
				}
			}
		}
		$language = isset( $_POST['language'] ) ? sanitize_text_field( $_POST['language'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$email    = isset( $_POST['user_email'] ) ? sanitize_email( strtolower( $_POST['user_email'] ) ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$name     = ( isset( $_POST['user_name'] ) && $_POST['user_name'] ) ? sanitize_text_field( $_POST['user_name'] ) : 'Sir/Madam';//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$mobile   = ( isset( $_POST['user_mobile'] ) && $_POST['user_mobile'] ) ? sanitize_text_field( $_POST['user_mobile'] ) : '';//phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $email || ! is_email( $email ) ) {
			wp_send_json(
				array(
					'allow_spin' => esc_html__( 'Email is invalid', 'wordpress-lucky-wheel' ),
				)
			);
		}
		/*Option white and black list*/
		$choose_list = $this->settings->get_params( 'choose_using_white_black_list' );
		$list_text   = $this->settings->get_params( $choose_list );

		if ( ! empty( $list_text ) ) {
			$lines_domain_email     = explode( "\n", $list_text );
			$lines_domain_email     = array_map( 'trim', $lines_domain_email );
			$lines_domain_email     = array_map( 'strtolower', $lines_domain_email );
			$explode_email_to_check = explode( '@', $email );
			$email_to_check         = $explode_email_to_check[1] ?? '';

			switch ( $choose_list ) {
				case 'white_list':
					if ( ! in_array( $email_to_check, $lines_domain_email ) ) {
						wp_send_json(
							array(
								'allow_spin' => esc_html__( 'Your email is not allowed', 'woocommerce-lucky-wheel' ),
							)
						);
					}
					break;
				case 'black_list':
					if ( in_array( $email_to_check, $lines_domain_email ) ) {
						wp_send_json(
							array(
								'allow_spin' => esc_html__( 'Your email is not allowed', 'woocommerce-lucky-wheel' ),
							)
						);
					}
					break;
			}
		}
		/*End option white and black list */
//		if ( wp_is_mobile() ) {
//			if ( 'on' == $this->settings->get_params( 'custom_field_name_enable_mobile' ) ) {
//				if ( ! $name && ( 'on' == $this->settings->get_params( 'custom_field_name_required' ) ) ) {
//					wp_send_json(
//						array(
//							'allow_spin' => esc_html__( 'Name is required', 'wordpress-lucky-wheel' ),
//						)
//					);
//				}
//			}
//			if ( 'on' == $this->settings->get_params( 'custom_field_mobile_enable_mobile' ) ) {
//				if ( ! $mobile && 'on' == $this->settings->get_params( 'custom_field_mobile_required' ) ) {
//					wp_send_json(
//						array(
//							'allow_spin' => esc_html__( 'Mobile is required', 'wordpress-lucky-wheel' ),
//						)
//					);
//				}
//				if ( ! self::is_phone( $mobile ) ) {
//					wp_send_json(
//						array(
//							'allow_spin' => esc_html__( 'Mobile field is not a valid phone number.', 'wordpress-lucky-wheel' ),
//						)
//					);
//				}
//			}
//		} else {
			if ( ! $name && 'on' == $this->settings->get_params( 'custom_field_name_required' )&&
			     (!empty($_POST['is_desktop']) || ('on' == $this->settings->get_params( 'custom_field_name_enable_mobile' )) )  ) {
				wp_send_json(
					array(
						'allow_spin' => esc_html__( 'Name is required', 'wordpress-lucky-wheel' ),
					)
				);
			}
			if ( ! $mobile && 'on' == $this->settings->get_params( 'custom_field_mobile_required' ) &&
                 (!empty($_POST['is_desktop']) || ('on' == $this->settings->get_params( 'custom_field_mobile_enable_mobile' )) ) ) {
				wp_send_json(
					array(
						'allow_spin' => esc_html__( 'Mobile is required', 'wordpress-lucky-wheel' ),
					)
				);
			}
			if ( $mobile && ! self::is_phone( $mobile ) ) {
				wp_send_json(
					array(
						'allow_spin' => esc_html__( 'Mobile field is not a valid phone number.', 'wordpress-lucky-wheel' ),
					)
				);
			}
//		}

		$date_format = get_option( 'date_format', 'F d, Y' );
		$date        = new DateTime();
		$today       = $date->format( $date_format );
		$allow       = 'no';
		$email_delay = $this->settings->get_params( 'general', 'delay' );
		switch ( $this->settings->get_params( 'general', 'delay_unit' ) ) {
			case 'm':
				$email_delay *= 60;
				break;
			case 'h':
				$email_delay *= 60 * 60;
				break;
			case 'd':
				$email_delay *= 60 * 60 * 24;
				break;
			default:
		}
		$stop                = - 1;
		$result              = 'lost';
		$result_notification = $this->settings->get_params( 'result', 'notification', $language )['lost'];
		$now                 = time();
		$wheel               = $this->settings->get_params( 'wheel' );
		$custom_label        = $this->settings->get_params( 'wheel', 'custom_label', $language );
		$email_templates     = $this->settings->get_params( 'wheel', 'email_templates', $language );

		$url_redirect_after_spin = $url_redirect_lost;

		if (!isset($wheel['probability']) || !is_array($wheel['probability'])){
			$wheel['probability'] = [];
		}
		if (!isset($wheel['prize_type']) || !is_array($wheel['prize_type'])){
			$wheel['prize_type'] = [];
		}
		if ( isset( $wheel['prize_quantity'] ) ) {
			if (!is_array($wheel['prize_quantity'])){
				$wheel['prize_quantity'] = [];
			}
			$prize_quantity = $wheel['prize_quantity'];
			foreach ( $wheel['prize_type'] as $count => $v ) {
				if ( $wheel['prize_type'][ $count ] !== 'non' ) {
					if ( isset($prize_quantity[ $count ] ) && $prize_quantity[ $count ] == 0 ) {
						$wheel['probability'][ $count ] = 0;
					}
				}
			}
		}

		#region Email
		$arr_name   = explode( ' ', $name );
		$first_name = $name;
		$last_name  = '';
		if ( count( $arr_name ) > 1 ) {
			$first_name = $arr_name[0];
			unset( $arr_name[0] );/*Delete first name*/
			$last_name = implode( ' ', $arr_name );
		}
		if ( $this->settings->get_params( 'general', 'enable' ) !== 'on' ) {
			$allow = 'Wrong email.';
			$data  = array( 'allow_spin' => $allow );
			wp_send_json( $data );
		}
		/*Mailchimp*/
		if ( $this->settings->get_params( 'mailchimp', 'enable' ) == 'on' ) {
			$mailchimp         = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Mailchimp();
			$mailchimp_list_id = $this->settings->get_params( 'mailchimp', 'lists', $language );
			$mailchimp->add_email( $email, $mailchimp_list_id, $first_name, $last_name, $mobile );
		}
		/*Active Campaign*/
		if ( 'on' == $this->settings->get_params( 'active_campaign', 'enable' ) && class_exists( 'VI_WORDPRESS_LUCKY_WHEEL_Admin_Active_Campaign' ) ) {
			$active_campaign = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Active_Campaign();
			if ( $this->settings->get_params( 'active_campaign', 'list', $language ) ) {
				$active_campaign->contact_add( $email, $this->settings->get_params( 'active_campaign', 'list' ), $name, '', $mobile );
			} else {
				$active_campaign->contact_add( $email, '', $first_name, $last_name, $mobile );
			}
		}
		/*Sendgrid*/
		if ( 'on' == $this->settings->get_params( 'sendgrid', 'enable' ) && class_exists( 'VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendgrid' ) ) {
			$sendgrid = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendgrid();
			$sendgrid->add_recipient( $email, $name );
			$sendgrid_list = $this->settings->get_params( 'sendgrid', 'list' );
			if ( $sendgrid_list && $sendgrid_list != 'none' ) {
				$time = time() + 60;
				wp_schedule_single_event(
					$time, 'wplwl_schedule_add_recipient_to_list', array(
						$email,
						$sendgrid_list,
					)
				);
			}
		}
		/*Metrilo*/
		$data = array();
		if ( $this->settings->get_params( 'metrilo_enable' ) ) {
			$metrilo                  = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Metrilo();
			$data['metrilo_response'] = $metrilo->contact_add( $email, $first_name, $last_name, $language );
		}
		/*Hubspot*/
		if ( $this->settings->get_params( 'enable_hubspot' ) && class_exists( 'VI_WORDPRESS_LUCKY_WHEEL_Admin_Hubspot' ) ) {
			$hubspot = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Hubspot();
			$hubspot->add_recipient( $email, $first_name, $last_name, $mobile );
		}
		/*Klaviyo*/
		if ( $this->settings->get_params( 'enable_klaviyo' ) && class_exists( 'VI_WORDPRESS_LUCKY_WHEEL_Admin_Klaviyo' ) ) {
			$klaviyo      = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Klaviyo();
			$klaviyo_list = $this->settings->get_params( 'klaviyo_list' );
			$klaviyo->add_recipient( $email, $klaviyo_list, $first_name, $last_name, $mobile );
		}
		/*Sendinblue*/
		if ( $this->settings->get_params( 'enable_sendinblue' ) && class_exists( 'VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendinblue' ) ) {
			$sendinblue      = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendinblue();
			$sendinblue_list = $this->settings->get_params( 'sendinblue_list' );
			$sendinblue_list = array_map( 'absint', $sendinblue_list );

			$sendinblue->add_recipient( $email, $sendinblue_list, $first_name, $last_name, $mobile );
		}
		/*MailPoet*/
		if ( $this->settings->get_params( 'enable_mailpoet' ) && class_exists( \MailPoet\API\API::class ) ) {
			$mailpoet_api           = \MailPoet\API\API::MP( 'v1' );
			$mailpoet_selected_list = $this->settings->get_params( 'mailpoet_list' );
			$mailpoet_selected_list = array_map( 'absint', $mailpoet_selected_list );

			try {
				$mailpoet_api->addSubscriber(
					[
						'email'  => $email,
						'status' => 'subscribed'
					],
					$mailpoet_selected_list
				);
			} catch ( \MailPoet\API\MP\v1\APIException $e ) {
			}
		}
		/*Mailster*/
		if ( $this->settings->get_params( 'enable_mailster' ) && function_exists( 'mailster' ) ) {
			// define to overwrite existing users
			$overwrite = true;

			// add with double opt in
			$double_opt_in = true;

			// prepare the userdata from a $_POST request. only the email is required
			$user_mailster_data = array(
				'email'     => $email,
				'firstname' => $first_name,
				'lastname'  => $last_name,
				'status'    => 1,
			);

			// add a new subscriber and $overwrite it if exists
			$subscriber_mailster_id = mailster( 'subscribers' )->add( $user_mailster_data, $overwrite );

			// if result isn't a WP_error assign the lists
			if ( ! is_wp_error( $subscriber_mailster_id ) ) {

				// your list ids
				$list_mailster_ids = $this->settings->get_params( 'mailster_list' ) ?? [];
				if ( ! empty( $list_mailster_ids ) ) {
					mailster( 'subscribers' )->assign_lists( $subscriber_mailster_id, $list_mailster_ids );
				}

			} else {
				// actions if adding fails. $subscriber_id is a WP_Error object
			}
		}

		/*Sendy*/
		if ( $this->settings->get_params( 'enable_sendy' ) && class_exists( 'VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendy' ) ) {
			$sendy      = new VI_WORDPRESS_LUCKY_WHEEL_Admin_Sendy();
			$sendy_list = $this->settings->get_params( 'sendy_list' );
			$sendy->add_subscribe( $email, $sendy_list, $name );

		}

		/*FunnelKit*/
		if ( $this->settings->get_params( 'enable_funnelkit' ) && class_exists( 'BWFCRM_Contact' ) ) {
			$contact_obj             = BWF_Contacts::get_instance();
			$funnelkit_selected_list = $this->settings->get_params( 'funnelkit_list' );
			$funnelkit_status        = $this->settings->get_params( 'funnelkit_status' );

			$contact = $contact_obj->get_contact_by( 'email', $email );
			if ( 0 === $contact->get_id() ) {
				/** New contact */
				! empty( $email ) && $contact->set_email( $email );
				$contact->set_creation_date( date( 'Y-m-d H:i:s' ) );//phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			} else {
				! empty( $funnelkit_selected_list ) && $contact->set_lists( $funnelkit_selected_list );
			}
			! empty( $name ) && $contact->set_l_name( $name );
			! empty( $mobile ) && $contact->set_contact_no( $mobile );
			! empty( $funnelkit_selected_list ) && $contact->set_lists( $funnelkit_selected_list );

			/**
			 * Contact status
			 * 0 - Unverified
			 * 1 - Subscribed
			 * 2 - Bounced
			 */
			isset( $funnelkit_status ) && $contact->set_status( absint( $funnelkit_status ) );
			do_action( 'wplwl_funnelkit_api_email', $contact, $email, $name, $mobile );
			/** Save contact */
			$contact->save();
		}
		do_action( 'wplwl_end_api_email', $email, $name, $mobile );
		#endregion Email

		if ( $this->new_data_updated ) {
			/*New data save email */
			$get_email = $this->email_table->get_row( $email );
			if ( ! empty( $get_email ) ) {
				$active = $get_email['active'] ?? 0;

				/*Check if email is not active*/
				if ( ! $active ) {
					$allow = esc_html__( 'Sorry, this email is marked as spam now. Please enter another email to continue.', 'wordpress-lucky-wheel' );
					wp_reset_postdata();
					$data = array( 'allow_spin' => $allow );
					wp_send_json( $data );
					die;
				}

				$email_id          = ! empty( $get_email['id'] ) ? maybe_unserialize( $get_email['id'] ) : '';
				$date_created      = ! empty( $get_email['date_created'] ) ? maybe_unserialize( $get_email['date_created'] ) : '';
				$old_mobile        = ! empty( $get_email['email_mobile'] ) ? maybe_unserialize( $get_email['email_mobile'] ) : '';
				$old_name          = ! empty( $get_email['email_name'] ) ? maybe_unserialize( $get_email['email_name'] ) : '';
				$spin_meta         = ! empty( $get_email['spin_times'] ) ? maybe_unserialize( $get_email['spin_times'] ) : [];
				$email_coupons     = ! empty( $get_email['email_coupons'] ) ? maybe_unserialize( $get_email['email_coupons'] ) : [];
				$email_labels      = ! empty( $get_email['email_labels'] ) ? maybe_unserialize( $get_email['email_labels'] ) : [];
				$new_spin_meta     = $spin_meta;
				$new_email_coupons = $email_coupons;
				$new_email_labels  = $email_labels;
				$new_mobile        = $old_mobile;
				$new_name          = $name;
				if ( ! $old_mobile && $mobile ) {
					$new_mobile = $mobile;
				}
				$spin_meta['spin_num'] = $spin_meta['spin_num'] ?? 0;
				$spin_meta['last_spin'] = $spin_meta['last_spin'] ?? 0;
				$spin_meta['total_spins'] = $spin_meta['total_spins'] ?? 0;
				if ( $spin_meta['spin_num'] >= $this->settings->get_params( 'general', 'spin_num' ) ) {
					$allow = esc_html__( 'This email has reach the maximum spins.', 'wordpress-lucky-wheel' );
				} elseif ( ( $now - $spin_meta['last_spin'] ) < $email_delay ) {
					$wait      = $email_delay + $spin_meta['last_spin'] - $now;
					$wait_day  = floor( $wait / 86400 );
					$wait_hour = floor( ( $wait - $wait_day * 86400 ) / 3600 );
					$wait_min  = floor( ( $wait - $wait_day * 86400 - $wait_hour * 3600 ) / 60 );
					$wait_sec  = $wait - $wait_day * 86400 - $wait_hour * 3600 - $wait_min * 60;

					$wait_return = $wait_sec . esc_html__( ' seconds', 'wordpress-lucky-wheel' );
					if ( $wait_day ) {
						$wait_return = sprintf( esc_html( '%1$s days %2$s hours %3$s minutes %4$s seconds' ), esc_html( $wait_day ), esc_html( $wait_hour ), esc_html( $wait_min ), esc_html( $wait_sec ) );
					} elseif ( $wait_hour ) {
						$wait_return = sprintf( esc_html( '%1$s hours %2$s minutes %3$s seconds' ), esc_html( $wait_hour ), esc_html( $wait_min ), esc_html( $wait_sec ) );
					} elseif ( $wait_min ) {
						$wait_return = sprintf( esc_html( '%1$s minutes %2$s seconds' ), esc_html( $wait_min ), esc_html( $wait_sec ) );
					}
					$allow = esc_html__( 'You have to wait ', 'wordpress-lucky-wheel' ) . esc_html( $wait_return ) . esc_html__( ' to be able to spin again.', 'wordpress-lucky-wheel' );
				} else {
					$allow = 'yes';
					$spin_meta['spin_num'] ++;
					$spin_meta['total_spins'] ++;
					$new_spin_meta = array(
						'spin_num'    => $spin_meta['spin_num'],
						'total_spins' => $spin_meta['total_spins'],
						'last_spin'   => $now,
						'gdpr'        => 1

					);
					$stop          = self::get_result( $wheel );
					if ( $wheel['prize_type'][ $stop ] != 'non' ) {
						$result              = 'win';
						$result_notification = $this->settings->get_params( 'result', 'notification', $language )['win'];
						$wheel_label         = $custom_label[ $stop ];

						$url_redirect_after_spin = $url_redirect_win;

						$code           = $wheel['custom_value'][ $stop ];
						$email_template = isset( $email_templates[ $stop ] ) ? $email_templates[ $stop ] : '';
						$this->send_email( $email, $name, $mobile, $code, $wheel_label, $language, $email_template );

						$new_email_coupons[] = $code;


						$new_email_labels[] = $wheel_label;

						$result_notification = str_replace( '{prize_value}', '<strong>' . $code . '</strong>', $result_notification );
						$result_notification = str_replace( '{prize_label}', '<strong>' . $wheel_label . '</strong>', $result_notification );
						$result_notification = str_replace( '{customer_name}', '<strong>' . ( isset( $_POST['user_name'] ) ? sanitize_text_field( $_POST['user_name'] ) : '' ) . '</strong>', $result_notification );//phpcs:ignore WordPress.Security.NonceVerification.Missing
						$result_notification = str_replace( '{customer_email}', '<strong>' . $email . '</strong>', $result_notification );
						$result_notification = str_replace( '{customer_mobile}', '<strong>' . $mobile . '</strong>', $result_notification );
						$result_notification = str_replace( '{today}', '<strong>' . $today . '</strong>', $result_notification );
						$result_notification = str_replace( '{quantity_label}', '', $result_notification );
						$result_notification = str_replace( array( '\n', '/n' ), ' ', $result_notification );
						if ( isset( $wheel['prize_quantity'] ) ) {
							$prize_quantity_left = intval( $wheel['prize_quantity'][ $stop ] );
							if ( $prize_quantity_left > 0 ) {
								$params                                     = $this->settings->get_params();
								$params['wheel']['prize_quantity'][ $stop ] = $prize_quantity_left - 1;
								update_option( '_wplwl_settings', $params );
								$this->send_email_no_prize_left( $params );
							}
						}
					}
				}
				$this->email_table->update( $email, $date_created, $new_mobile, $new_name, $new_spin_meta, $new_email_coupons, $new_email_labels, 1 );
			} else {
				$allow = 'yes';
				//save email
				$stop           = self::get_result( $wheel );
				$email_coupons  = array();
				$email_labels   = array();
				$wheel_label    = $custom_label[ $stop ];
				$email_template = isset( $email_templates[ $stop ] ) ? $email_templates[ $stop ] : '';
				$spin_times     = array(
					'spin_num'    => 1,
					'total_spins' => 1,
					'last_spin'   => $now,
					'gdpr'        => 1
				);
				if ( $wheel['prize_type'][ $stop ] != 'non' ) {
					$result                  = 'win';
					$url_redirect_after_spin = $url_redirect_win;
					$result_notification     = $this->settings->get_params( 'result', 'notification', $language )['win']??'';

					$code = isset($wheel['custom_value'][ $stop ]) ? $wheel['custom_value'][ $stop ]:'';
					$this->send_email( $email, $name, $mobile, $code, $wheel_label, $language, $email_template );

					$email_coupons[] = $code;
					$email_labels[]  = $wheel_label;


					$result_notification = str_replace( '{prize_value}', '<strong>' . $code . '</strong>', $result_notification );
					$result_notification = str_replace( '{prize_label}', '<strong>' . $wheel_label . '</strong>', $result_notification );
					$result_notification = str_replace( '{customer_name}', '<strong>' . ( isset( $_POST['user_name'] ) ? sanitize_text_field( $_POST['user_name'] ) : '' ) . '</strong>', $result_notification );//phpcs:ignore WordPress.Security.NonceVerification.Missing
					$result_notification = str_replace( '{customer_email}', '<strong>' . $email . '</strong>', $result_notification );
					$result_notification = str_replace( '{customer_mobile}', '<strong>' . $mobile . '</strong>', $result_notification );
					$result_notification = str_replace( '{today}', '<strong>' . $today . '</strong>', $result_notification );
					$result_notification = str_replace( '{quantity_label}', '', $result_notification );
					$result_notification = str_replace( array( '\n', '/n' ), ' ', $result_notification );
					if ( isset( $wheel['prize_quantity'] ) ) {
						$prize_quantity_left = intval( $wheel['prize_quantity'][ $stop ] );
						if ( $prize_quantity_left > 0 ) {
							$params                                     = $this->settings->get_params();
							$params['wheel']['prize_quantity'][ $stop ] = $prize_quantity_left - 1;
							update_option( '_wplwl_settings', $params );
							$this->send_email_no_prize_left( $params );
						}
					}
				}
				$email_id = $this->email_table->insert( $email, $now, $mobile, $name, $spin_times, $email_coupons, $email_labels );
			}
		} else {
			$allow = esc_html__( 'Sorry, Update is in progress, please try again later.', 'wordpress-lucky-wheel' );
			wp_reset_postdata();
			$data = array( 'allow_spin' => $allow );
			wp_send_json( $data );
			die;
		}


		$data = array(
			'allow_spin'          => $allow,
			'stop_position'       => $stop,
			'result_notification' => do_shortcode( $result_notification ),
			'result'              => $result,
		);
		if ( $redirect_after_spin ) {
			$data['url_redirect_after_spin'] = $url_redirect_after_spin;
		}
		wp_send_json( $data );
	}

	public static function bubbles_html( $images ) {
		?>
        <div class="wplwl-background-effect-floating-bubbles" aria-hidden="true">
			<?php
			for ( $i = 1; $i <= 16; $i ++ ) {
				?>
                <div class="wplwl-bubble <?php echo esc_attr( "wplwl-bubble-x{$i}" ) ?>"><img
                            src="<?php echo esc_url( VI_WORDPRESS_LUCKY_WHEEL_IMAGES . 'falling-snow/' . ( isset( $images[ $i ] ) ? $images[ $i ] : $images[ wp_rand( 0, count( $images ) - 1 ) ] ) . '.png' ) ?>">
                </div>
				<?php
			}
			?>
        </div>
		<?php
	}

	public static function snowflake_html() {
		?>
        <div class="wplwl-background-effect-snowflakes" aria-hidden="true">
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❆
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❄
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❆
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❄
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❆
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❄
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❆
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❄
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❆
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❄
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❅
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❆
            </div>
            <div class="wplwl-background-effect-snowflake">
                ❄
            </div>
        </div>
		<?php
	}

	public static function snowflake_1_html() {
		?>
        <div class="wplwl-background-effect-snowflakes-1" aria-hidden="true">
			<?php
			for ( $i = 0; $i < 42; $i ++ ) {
				?>
                <span></span>
				<?php
			}
			?>
        </div>
		<?php
	}

	public static function get_result( $wheel ) {
		return WPLWL_Wheel_Engine::get_result( $wheel );
	}

	/**
	 * Validates a phone number using a regular expression.
	 *
	 * @param string $phone Phone number to validate.
	 *
	 * @return bool
	 */
	public static function is_phone( $phone ) {
		if ( 0 < strlen( trim( preg_replace( '/[\s\#0-9_\-\+\/\(\)\.]/', '', $phone ) ) ) ) {
			return false;
		}

		return true;
	}
}