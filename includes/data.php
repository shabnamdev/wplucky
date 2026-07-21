<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WORDPRESS_LUCKY_WHEEL_DATA {
	private $params;
	private $default;
	protected static $instance = null, $allow_html = null;

	/**
	 * VI_WORDPRESS_LUCKY_WHEEL_DATA constructor.
	 * Init setting
	 */
	public function __construct() {
		global $wp_lucky_wheel_settings;
		if ( ! $wp_lucky_wheel_settings ) {
			$wp_lucky_wheel_settings = get_option( '_wplwl_settings', array() );
		}
		$this->default = array(
			'general'                             => array(
				'enable'     => "on",
				'mobile'     => "on",
				'spin_num'   => 1,
				'delay'      => 24,
				'delay_unit' => 'h'
			),
			'notify'                              => array(
				'position'                 => 'bottom-right',
				'size'                     => 40,
				'color'                    => '',
				'popup_icon'               => '',
				'popup_icon_color'         => '',
				'popup_icon_bg_color'      => '',
				'popup_icon_border_radius' => 0,
				'intent'                   => 'popup_icon',
				'hide_popup'               => 'off',
				'show_wheel'               => '1,5',//initial time
				'scroll_amount'            => '50',
				'show_again'               => 24,
				'show_again_unit'          => 'h',
				'show_only_front'          => 'off',
				'show_only_blog'           => 'off',
				'show_only_shop'           => 'off',
				'conditional_tags'         => '',
				'time_on_close'            => '1',
				'time_on_close_unit'       => 'd',
			),
			'wheel_wrap'                          => array(
				'description'            => '<h2><span style="color: #ffffff;">SPIN TO WIN!</span></h2>
<ul>
 	<li><em><span style="color: #dbdbdb;">Try your lucky to get discount coupon</span></em></li>
 	<li><em><span style="color: #dbdbdb;">1 spin per email</span></em></li>
 	<li><em><span style="color: #dbdbdb;">No cheating</span></em></li>
</ul>',
				'bg_image'               => VI_WORDPRESS_LUCKY_WHEEL_IMAGES . '2020.png',
				'bg_color'               => '#a77e44',
				'text_color'             => '#ffffff',
				'spin_button'            => 'Try Your Lucky',
				'spin_button_color'      => '#000000',
				'spin_button_bg_color'   => '#ffbe10',
				'pointer_position'       => 'center',
				'pointer_color'          => '#f70707',
				'wheel_center_image'     => '',
				'wheel_center_color'     => '#ffffff',
				'wheel_border_color'     => '#ffffff',
				'wheel_dot_color'        => '#000000',
				'close_option'           => 'on',
				'font'                   => '',
				'gdpr'                   => 'off',
				'gdpr_message'           => 'I agree with the <a href="">term and condition</a>',
				'custom_css'             => '',
				'congratulations_effect' => 'firework',
				'background_effect'      => 'snowflakes',
			),
			'wheel'                               => array(
				'spinning_time'     => 5,
				'wheel_speed'       => 3,
				'prize_type'        => array(
					"non",
					"custom",
					"non",
					"custom",
					"non",
					"custom",
					"non",
					"custom",
					"non",
					"custom",
					"non",
					"custom"
				),
				'custom_value'      => array(
					"",
					"prize",
					"",
					"prize",
					"",
					"prize",
					"",
					"prize",
					"",
					"prize",
					"",
					"prize"
				),
				'custom_label'      => array(
					"Not Lucky",
					"Prize",
					"Not Lucky",
					"Prize",
					"Not Lucky",
					"Prize",
					"Not Lucky",
					"Prize",
					"Not Lucky",
					"Prize",
					"Not Lucky",
					"Prize"
				),
				'prize_quantity'    => array( - 1, - 1, - 1, - 1, - 1, - 1, - 1, - 1, - 1, - 1, - 1, - 1 ),
				'probability'       => array( '10', '10', '10', '5', '10', '5', '10', '10', '10', '10', '5', '5' ),
				'bg_color'          => array(
					'#ffe0b2',
					'#e65100',
					'#ffb74d',
					'#fb8c00',
					'#ffe0b2',
					'#e65100',
					'#ffb74d',
					'#fb8c00',
					'#ffe0b2',
					'#e65100',
					'#ffb74d',
					'#fb8c00',
				),
				'slices_text_color' => array(
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
					'#fff',
				),
				'slice_text_color'  => '#fff',
				'show_full_wheel'   => 'off',
				'font_size'         => '100',
				'wheel_size'        => '100',
				'random_color'      => 'off',
				'quantity_label'    => '({prize_quantity} left)',
				'email_templates'   => [],
			),
			'result'                              => array(
				'auto_close'   => 0,
				'email'        => array(
					'from_name'             => '',
					'from_address'          => '',
					'subject'               => 'Wordpress lucky wheel award',
					'heading'               => 'Congratulations!',
					'content'               => "Dear {customer_name},\n You have won the {prize_label}. The code is {prize_value}. Please use this code and contact with us to receive the prize. Thank you.\n Your Sincerely!",
					'header_image'          => '',
					'footer_text'           => '',
					'base_color'            => '#a1fbf2',
					'background_color'      => '#5b9dd9',
					'body_background_color' => '#ffffff',
					'body_text_color'       => '#0f0f0f',
				),
				'notification' => array(
					'win'  => 'Congratulations! You have won a {prize_label}. Code was sent to {customer_email}. Please check your inbox. Thank you!',
					'lost' => 'Just almost win. Maybe you\'ll be lucky next time.',
				),
				'admin_email'  => array(
					'enable'  => 'off',
					'to'      => '',
					'subject' => 'Wordpress Lucky Wheel notification',
					'heading' => 'Wordpress Lucky Wheel',
					'content' => 'A customer with email {customer_email} has just won {prize_label}. Coupon code is : {prize_value}. Please moderate.'
				)
			),
			'mailchimp'                           => array(
				'enable'       => 'off',
				'double_optin' => 'off',
				'api_key'      => '',
				'lists'        => ''
			),
			'active_campaign'                     => array(
				'enable' => 'off',
				'key'    => '',
				'url'    => '',
				'list'   => '',
			),
			'key'                                 => '',
			'button_shop_title'                   => 'Shop now',
			'button_shop_url'                     => get_bloginfo( 'url' ),
			'button_shop_color'                   => '#fff',
			'button_shop_bg_color'                => '#000',
			'button_shop_size'                    => '20',
			'sendgrid'                            => array(
				'enable' => 'off',
				'key'    => '',
				'list'   => 'none',
			),
			'ajax_endpoint'                       => 'ajax',
			'custom_field_email_enable'           => 'on',
			'custom_field_mobile_enable'          => 'off',
			'custom_field_mobile_enable_mobile'   => 'off',
			'custom_field_mobile_required'        => 'off',
			'custom_field_mobile_phone_countries' => [ 0 ],
			'custom_field_name_enable'            => 'on',
			'custom_field_name_enable_mobile'     => 'on',
			'custom_field_name_required'          => 'off',
			'reset_spins_interval'                => 0,
			'reset_spins_hour'                    => 0,
			'reset_spins_minute'                  => 0,
			'reset_spins_second'                  => 0,
			'metrilo_enable'                      => '',
			'metrilo_token'                       => '',
			'metrilo_tag'                         => '',
			'metrilo_subscribed'                  => '',
			'enable_hubspot'                      => '',
			'hubspot_api'                         => '',
			'enable_klaviyo'                      => '',
			'klaviyo_api'                         => '',
			'klaviyo_list'                        => '',
			'enable_sendinblue'                   => '',
			'sendinblue_api'                      => '',
			'sendinblue_list'                     => [],
			'enable_sendy'                        => '',
			'sendy_api'                           => '',
			'sendy_login_url'                     => '',
			'sendy_brand'                         => '',
			'sendy_list'                          => '',
			'enable_mailster'                     => '',
			'mailster_list'                       => [],
			'enable_funnelkit'                    => '',
			'funnelkit_list'                      => [],
			'funnelkit_status'                    => '1',
			'wplwl_recaptcha_site_key'            => '',
			'wplwl_recaptcha_version'             => '3',
			'wplwl_recaptcha_secret_theme'        => 'light',
			'wplwl_recaptcha_secret_key'          => '',
			'wplwl_recaptcha'                     => '',
			'choose_using_white_black_list'       => 'black_list'
		);

		$this->params = wp_parse_args( $wp_lucky_wheel_settings, $this->default );
	}

	public static function get_instance( $new = false ) {
		if ( $new || null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_params( $name = '', $name_sub = '', $language = '', $default = false ) {
		if ( ! $name ) {
			return apply_filters( 'wp_lucky_wheel_params', $this->params );
		}
		$language = apply_filters( '_wplwl_settings_language', $language, $name, $name_sub );
		if ( $language && strpos( $language, '_' ) !== 0 ) {
			$language = '_' . $language;
		}
		$name_t = $name . $language;
		if ( $name_sub ) {
			$name_language = $name_sub . $language;
			$name_t        = $name . '__' . $name_sub . $language;
		}
		$name_filter = 'wp_lucky_wheel_params_' . $name_t;
		switch ( $name ) {
			case 'key':
				$name_filter = '';
				break;
			case 'use_custom_table':
				if ( ! isset( $this->params['use_custom_table'] ) ) {
					if ( get_option( 'wpwlwl_updated_database', 'notfound' ) === 'notfound' ) {
						WPLWL_EMAIL_Table::create_table();
						$count_posts = array_sum( (array) wp_count_posts( 'wplwl_email' ) );
						if ( ! $count_posts ) {
							update_option( 'wpwlwl_updated_database', current_time( 'U' ) );
							update_option( 'wplwl_run_action_updated', true );
						} else {
							update_option( 'wpwlwl_updated_database', '' );
						}
					}
					if ( get_option( 'wpwlwl_updated_database' ) ) {
						$this->params['use_custom_table'] = 1;
					}
				}
				break;
		}
		if ( ! isset( $result ) ) {
			if ( $name_sub ) {
				$result = isset( $this->params[ $name ] ) ? ( $this->params[ $name ][ $name_language ] ?? $this->params[ $name ][ $name_sub ] ?? $default ) : $default;
			} else {
				$result = $this->params[ $name_t ] ?? $this->params[ $name ] ?? $default;
			}
		}

		return $name_filter ? apply_filters( $name_filter, $result ) : $result;
	}

	public function get_default( $name = "", $name_sub = '' ) {
		if ( ! $name ) {
			return $this->default;
		} elseif ( isset( $this->default[ $name ] ) ) {
			if ( $name_sub ) {
				if ( isset( $this->default[ $name ][ $name_sub ] ) ) {
					return apply_filters( 'wp_lucky_wheel_params_default_' . $name . '__' . $name_sub, $this->default[ $name ] [ $name_sub ] );
				} else {
					return false;
				}
			} else {
				return apply_filters( 'wp_lucky_wheel_params_default_' . $name, $this->default[ $name ] );
			}
		} else {
			return false;
		}
	}

	/**
	 * @param $tags
	 *
	 * @return array
	 */
	public static function filter_allowed_html( $tags = [] ) {
		if ( self::$allow_html && empty( $tags ) ) {
			return self::$allow_html;
		}
		$tags = array_merge_recursive( $tags, wp_kses_allowed_html( 'post' ), array(
			'input'  => array(
				'type'         => 1,
				'id'           => 1,
				'name'         => 1,
				'class'        => 1,
				'placeholder'  => 1,
				'autocomplete' => 1,
				'style'        => 1,
				'value'        => 1,
				'size'         => 1,
				'checked'      => 1,
				'disabled'     => 1,
				'readonly'     => 1,
				'data-*'       => 1,
			),
			'form'   => array(
				'method' => 1,
				'id'     => 1,
				'class'  => 1,
				'action' => 1,
				'data-*' => 1,
			),
			'select' => array(
				'id'       => 1,
				'name'     => 1,
				'class'    => 1,
				'multiple' => 1,
				'data-*'   => 1,
			),
			'option' => array(
				'value'    => 1,
				'selected' => 1,
				'data-*'   => 1,
			),
			'style'  => array(
				'id'    => 1,
				'class' => 1,
				'type'  => 1,
			),
			'source' => array(
				'type' => 1,
				'src'  => 1
			),
			'video'  => array(
				'width'  => 1,
				'height' => 1,
				'src'    => 1
			),
			'iframe' => array(
				'width'           => 1,
				'height'          => 1,
				'allowfullscreen' => 1,
				'allow'           => 1,
				'src'             => 1
			),
		) );
		$tmp = $tags;
		foreach ( $tmp as $key => $value ) {
			if ( in_array( $key, array( 'div', 'span', 'a', 'form', 'select', 'option', 'table', 'tr', 'th', 'td' ) ) ) {
				$tags[ $key ] = wp_parse_args( [
					'width'  => 1,
					'height' => 1,
					'class'  => 1,
					'id'     => 1,
					'type'   => 1,
					'style'  => 1,
					'data-*' => 1,
				],$value);
			}
		}
		self::$allow_html = $tags;

		return self::$allow_html;
	}

	public static function def_phone_country() {
		return '{"BD":"+880","BE":"+32","BF":"+226","BG":"+359","BA":"+387","BB":"+1246","WF":"+681","BL":"+590","BM":"+1441","BN":"+673","BO":"+591","BH":"+973","BI":"+257","BJ":"+229","BT":"+975","JM":"+1876","BV":"","BW":"+267","WS":"+685","BQ":"+599","BR":"+55","BS":"+1242","JE":"+441534","BY":"+375","BZ":"+501","RU":"+7","RW":"+250","RS":"+381","TL":"+670","RE":"+262","TM":"+993","TJ":"+992","RO":"+40","TK":"+690","GW":"+245","GU":"+1671","GT":"+502","GS":"","GR":"+30","GQ":"+240","GP":"+590","JP":"+81","GY":"+592","GG":"+441481","GF":"+594","GE":"+995","GD":"+1473","GB":"+44","GA":"+241","SV":"+503","GN":"+224","GM":"+220","GL":"+299","GI":"+350","GH":"+233","OM":"+968","TN":"+216","JO":"+962","HR":"+385","HT":"+509","HU":"+36","HK":"+852","HN":"+504","HM":"","VE":"+58","PR":["+1787","+1939"],"PS":"+970","PW":"+680","PT":"+351","SJ":"+47","PY":"+595","IQ":"+964","PA":"+507","PF":"+689","PG":"+675","PE":"+51","PK":"+92","PH":"+63","PN":"+870","PL":"+48","PM":"+508","ZM":"+260","EH":"+212","EE":"+372","EG":"+20","ZA":"+27","EC":"+593","IT":"+39","VN":"+84","SB":"+677","ET":"+251","SO":"+252","ZW":"+263","SA":"+966","ES":"+34","ER":"+291","ME":"+382","MD":"+373","MG":"+261","MF":"+590","MA":"+212","MC":"+377","UZ":"+998","MM":"+95","ML":"+223","MO":"+853","MN":"+976","MH":"+692","MK":"+389","MU":"+230","MT":"+356","MW":"+265","MV":"+960","MQ":"+596","MP":"+1670","MS":"+1664","MR":"+222","IM":"+441624","UG":"+256","TZ":"+255","MY":"+60","MX":"+52","IL":"+972","FR":"+33","IO":"+246","SH":"+290","FI":"+358","FJ":"+679","FK":"+500","FM":"+691","FO":"+298","NI":"+505","NL":"+31","NO":"+47","NA":"+264","VU":"+678","NC":"+687","NE":"+227","NF":"+672","NG":"+234","NZ":"+64","NP":"+977","NR":"+674","NU":"+683","CK":"+682","XK":"","CI":"+225","CH":"+41","CO":"+57","CN":"+86","CM":"+237","CL":"+56","CC":"+61","CA":"+1","CG":"+242","CF":"+236","CD":"+243","CZ":"+420","CY":"+357","CX":"+61","CR":"+506","CW":"+599","CV":"+238","CU":"+53","SZ":"+268","SY":"+963","SX":"+599","KG":"+996","KE":"+254","SS":"+211","SR":"+597","KI":"+686","KH":"+855","KN":"+1869","KM":"+269","ST":"+239","SK":"+421","KR":"+82","SI":"+386","KP":"+850","KW":"+965","SN":"+221","SM":"+378","SL":"+232","SC":"+248","KZ":"+7","KY":"+1345","SG":"+65","SE":"+46","SD":"+249","DO":["+1809","+1829","+1849"],"DM":"+1767","DJ":"+253","DK":"+45","VG":"+1284","DE":"+49","YE":"+967","DZ":"+213","US":"+1","UY":"+598","YT":"+262","UM":"+1","LB":"+961","LC":"+1758","LA":"+856","TV":"+688","TW":"+886","TT":"+1868","TR":"+90","LK":"+94","LI":"+423","LV":"+371","TO":"+676","LT":"+370","LU":"+352","LR":"+231","LS":"+266","TH":"+66","TF":"","TG":"+228","TD":"+235","TC":"+1649","LY":"+218","VA":"+379","VC":"+1784","AE":"+971","AD":"+376","AG":"+1268","AF":"+93","AI":"+1264","VI":"+1340","IS":"+354","IR":"+98","AM":"+374","AL":"+355","AO":"+244","AQ":"","AS":"+1684","AR":"+54","AU":"+61","AT":"+43","AW":"+297","IN":"+91","AX":"+35818","AZ":"+994","IE":"+353","ID":"+62","UA":"+380","QA":"+974","MZ":"+258"}';
	}


	/**
	 * Geolocate an IP address.
	 *
	 * @param string $ip_address IP Address.
	 * @param bool $fallback If true, fallbacks to alternative IP detection (can be slower).
	 * @param bool $api_fallback If true, uses geolocation APIs if the database file doesn't exist (can be slower).
	 *
	 * @return array
	 */
	public static function geolocate_ip( $ip_address = '' ) {
		// Filter to allow custom geolocation of the IP address.
		$country_code = apply_filters( 'wplwl_geolocate_ip', false, $ip_address );

		if ( false !== $country_code ) {
			return array(
				'country'  => $country_code,
				'state'    => '',
				'city'     => '',
				'postcode' => '',
			);
		}

		if ( empty( $ip_address ) ) {
			$ip_address   = self::get_ip_address();
			$country_code = self::get_country_code_from_headers();
		}

		/**
		 * Get geolocation filter.
		 *
		 * @param array $geolocation Geolocation data, including country, state, city, and postcode.
		 * @param string $ip_address IP Address.
		 *
		 * @since 3.9.0
		 */
		$geolocation = apply_filters(
			'wplwl_get_geolocation',
			array(
				'country'  => $country_code,
				'state'    => '',
				'city'     => '',
				'postcode' => '',
			),
			$ip_address
		);

		return array(
			'country'  => $geolocation['country'],
			'state'    => $geolocation['state'],
			'city'     => $geolocation['city'],
			'postcode' => $geolocation['postcode'],
		);
	}

	/**
	 * Get current user IP Address.
	 *
	 * @return string
	 */
	public static function get_ip_address() {
		if ( isset( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return (string) rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return '';
	}

	/**
	 * Fetches the country code from the request headers, if one is available.
	 *
	 * @return string The country code pulled from the headers, or empty string if one was not found.
	 */
	private static function get_country_code_from_headers() {
		$country_code = '';

		$headers = array(
			'MM_COUNTRY_CODE',
			'GEOIP_COUNTRY_CODE',
			'HTTP_CF_IPCOUNTRY',
			'HTTP_X_COUNTRY_CODE',
		);

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			$country_code = strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
			break;
		}

		return $country_code;
	}

	/**
	 * @param $icon
	 *
	 * @return string
	 */
	public static function get_gift_icon_class( $icon ) {
		return "wplwl_gift_icons-{$icon}";
	}

	/**All icons from file ../css/giftbox.css
	 * Prefix wplwl_gift_icons-
	 * @return array
	 */
	public static function get_gift_icons() {
		return array(
			'aniversary-giftbox',
			'big-giftbox-and-gift-with-heart',
			'big-giftbox-with-bun',
			'big-giftbox-with-lateral-lace',
			'big-giftbox-with-ribbon',
			'cylindrical-giftbox-with-ribbon',
			'gifbox-with-lace',
			'gifbox-with-ribbon-in-the-middle',
			'gifbox-with-ribbon-on-top',
			'gifbox-wrapped-with-ribbon',
			'gift-with-bow',
			'gift-with-ribbon',
			'giftbox-side',
			'giftbox-with-a-big-ribbon-on-cover',
			'giftbox-with-a-heart',
			'giftbox-with-a-heart-on-side',
			'giftbox-with-big-lace',
			'giftbox-with-big-lace-1',
			'giftbox-with-big-ribbon',
			'giftbox-with-big-ribbon-1',
			'giftbox-with-big-ribbon-2',
			'giftbox-with-big-ribbon-3',
			'giftbox-with-bun',
			'giftbox-with-flower',
			'giftbox-with-hearts',
			'giftbox-with-lace-on-a-side',
			'giftbox-with-long-ribbon',
			'giftbox-with-ribbon',
			'giftbox-with-ribbon-on-one-side',
			'giftbox-with-ribbon-on-top',
			'giftbox-with-ribbon-on-top-1',
			'giftbox-wrapped',
			'heart-shape-giftbox-with-lace',
			'heart-shape-giftbox-with-ribbon',
			'heart-shapped-gifbox-with-ribbon',
			'open-box-with-two-hearts',
			'open-gitfbox-with-two-hearts',
			'polka-dots-giftbox-with-lace',
			'rectangular-giftbox-with-flower',
			'round-gift-box-with-lace',
			'round-giftbox-with-flower',
			'square-gifbox-wrapped',
			'square-gifsoft-with-bun',
			'square-giftbox-with-big-lace',
			'square-giftbox-with-big-ribbon',
			'three-giftboxes-with-ribbon-and-heart',
			'two-gifboxes-tied-together',
			'two-gifboxes-wrapped',
			'two-giftboxes',
			'valentines-giftbox'
		);
	}

	public static function get_all_bg_effects() {
		return array(
			'none'               => esc_html__( 'None', 'wordpress-lucky-wheel' ),
			'floating-halloween' => esc_html__( 'Halloween', 'wordpress-lucky-wheel' ),
			'halloween-1'        => esc_html__( 'Halloween 1', 'wordpress-lucky-wheel' ),
			'halloween-2'        => esc_html__( 'Halloween 2', 'wordpress-lucky-wheel' ),
			'halloween-3'        => esc_html__( 'Halloween 3', 'wordpress-lucky-wheel' ),
			'leaf-1'             => esc_html__( 'Falling Leaves 1', 'wordpress-lucky-wheel' ),
			'leaf-2'             => esc_html__( 'Falling Leaves 2', 'wordpress-lucky-wheel' ),
			'hearts'             => esc_html__( 'Hearts', 'wordpress-lucky-wheel' ),
			'heart'              => esc_html__( 'Heart', 'wordpress-lucky-wheel' ),
			'smile'              => esc_html__( 'Smile', 'wordpress-lucky-wheel' ),
			'star'               => esc_html__( 'Star', 'wordpress-lucky-wheel' ),
			'floating-bubbles'   => esc_html__( 'Balloons', 'wordpress-lucky-wheel' ),
			'snowflakes'         => esc_html__( 'Snowflake', 'wordpress-lucky-wheel' ),
			'snowflakes-1'       => esc_html__( 'Snowflake 1', 'wordpress-lucky-wheel' ),
			'snowflakes-2-2'     => esc_html__( 'Snowflake 2', 'wordpress-lucky-wheel' ),
			'snowflakes-2-1'     => esc_html__( 'Snowballs', 'wordpress-lucky-wheel' ),
			'snowflakes-2-3'     => esc_html__( 'Blurred snows', 'wordpress-lucky-wheel' ),
			'random'             => esc_html__( 'Random', 'wordpress-lucky-wheel' ),
		);
	}

	public static function wplwl_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( [ __CLASS__, 'wplwl_clean' ], $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
	public static function implode_html_attributes( $raw_attributes ) {
		$attributes = array();
		foreach ( $raw_attributes as $name => $value ) {
			$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $attributes );
	}

	public static function villatheme_render_field( $name, $field ) {
		if ( ! $name ) {
			return;
		}
		if ( ! empty( $field['html'] ) ) {
			echo $field['html'];

//			echo wp_kses($field['html'], self::filter_allowed_html());
			return;
		}
		$type  = $field['type'] ?? '';
		$value = $field['value'] ?? '';
		if ( ! empty( $field['prefix'] ) ) {
			$id = "wplwl-{$field['prefix']}-{$name}";
		} else {
			$id = "wplwl-{$name}";
		}
		$class             = $field['class'] ?? $id;
		$custom_attributes = array_merge( [
			'type'  => $type,
			'name'  => $name,
			'id'    => $id,
			'value' => $value,
			'class' => $class,
		], (array) ( $field['custom_attributes'] ?? [] ) );
		if ( ! empty( $field['input_label'] ) ) {
			$input_label_type = $field['input_label']['type'] ?? 'left';
			echo wp_kses( sprintf( '<div class="vi-ui %s labeled input">', ( ! empty( $field['input_label']['fluid'] ) ? 'fluid ' : '' ) . $input_label_type ), self::filter_allowed_html() );
			if ( $input_label_type === 'left' ) {
				echo wp_kses( sprintf( '<div class="%s">%s</div>', $field['input_label']['label_class'] ?? 'vi-ui label', $field['input_label']['label'] ?? '' ), self::filter_allowed_html() );
			}
		}
		switch ( $type ) {
			case 'checkbox':
				unset( $custom_attributes['type'] );
				echo wp_kses( sprintf( '
					<div class="vi-ui toggle checkbox">
						<input type="hidden" %s>
						<input type="checkbox" id="%s-checkbox" %s ><label></label>
					</div>', self::implode_html_attributes( $custom_attributes ), $id, $value ? 'checked' : ''
				), self::filter_allowed_html() );
				break;
			case 'select':
				$select_options = $field['options'] ?? '';
				$multiple       = $field['multiple'] ?? '';
				unset( $custom_attributes['type'] );
				unset( $custom_attributes['value'] );
				$custom_attributes['class'] = "vi-ui fluid dropdown {$class}";
				if ( $multiple ) {
					$value                         = (array) $value;
					$custom_attributes['name']     = $name . '[]';
					$custom_attributes['multiple'] = "multiple";
				}
				echo wp_kses( sprintf( '<select %s>', self::implode_html_attributes( $custom_attributes ) ), self::filter_allowed_html() );
				if ( is_array( $select_options ) && count( $select_options ) ) {
					foreach ( $select_options as $k => $v ) {
						$selected = $multiple ? in_array( $k, $value ) : ( $k == $value );
						echo wp_kses( sprintf( '<option value="%s" %s>%s</option>',
							$k, $selected ? 'selected' : '', $v ), self::filter_allowed_html() );
					}
				}
				printf( '</select>' );
				break;
			case 'textarea':
				unset( $custom_attributes['type'] );
				unset( $custom_attributes['value'] );
				echo wp_kses( sprintf( '<textarea %s>%s</textarea>', self::implode_html_attributes( $custom_attributes ), $value ), self::filter_allowed_html() );
				break;
			default:
				if ( $type ) {
					echo wp_kses( sprintf( '<input %s>', self::implode_html_attributes( $custom_attributes ) ), self::filter_allowed_html() );
				}
		}
		if ( ! empty( $field['input_label'] ) ) {
			if ( ! empty( $input_label_type ) && $input_label_type === 'right' ) {
				printf( '<div class="%s">%s</div>', esc_attr( $field['input_label']['label_class'] ?? 'vi-ui label' ), wp_kses_post( $field['input_label']['label'] ?? '' ) );
			}
			printf( '</div>' );
		}
	}

	public static function villatheme_render_table_field( $options ) {
		if ( ! is_array( $options ) || empty( $options ) ) {
			return;
		}
		if ( ! empty( $options['html'] ) ) {
			echo wp_kses( $options['html'], self::filter_allowed_html() );

			return;
		}
		if ( isset( $options['section_start'] ) ) {
			if ( ! empty( $options['section_start']['accordion'] ) ) {
				echo wp_kses( sprintf( '<div class="vi-ui styled fluid accordion%s">
                                            <div class="title%s">
                                                <i class="dropdown icon"> </i>
                                                %s
                                            </div>
                                        <div class="content%s">',
					! empty( $options['section_start']['class'] ) ? " {$options['section_start']['class']}" : '',
					! empty( $options['section_start']['active'] ) ? " active" : '',
					$options['section_start']['title'] ?? '',
					! empty( $options['section_start']['active'] ) ? " active" : ''
				),
					self::filter_allowed_html() );
			}
			if ( empty( $options['fields_html'] ) ) {
				echo wp_kses_post( '<table class="form-table">' );
			}
		}
		if ( ! empty( $options['fields_html'] ) ) {
			echo wp_kses( $options['fields_html'], self::filter_allowed_html() );
		} else {
			$fields = $options['fields'] ?? '';
			if ( is_array( $fields ) && count( $fields ) ) {
				foreach ( $fields as $key => $param ) {
					$type = $param['type'] ?? '';
					$name = $param['name'] ?? $key;
					if ( ! $name ) {
						continue;
					}
					if ( ! empty( $param['prefix'] ) ) {
						$id = "wplwl-{$param['prefix']}-{$name}";
					} else {
						$id = "wplwl-{$name}";
					}
					if ( empty( $param['not_wrap_html'] ) ) {
						if ( ! empty( $param['wrap_class'] ) ) {
							printf( '<tr class="%s"><th><label for="%s">%s</label></th><td>',
								esc_attr( $param['wrap_class'] ), esc_attr( $type === 'checkbox' ? $id . '-' . $type : $id ), wp_kses_post( $param['title'] ?? '' ) );
						} else {
							printf( '<tr><th><label for="%s">%s</label></th><td>', esc_attr( $type === 'checkbox' ? $id . '-' . $type : $id ), wp_kses_post( $param['title'] ?? '' ) );
						}
					}
					do_action( 'wplwl_before_option_field', $name, $param );
					self::villatheme_render_field( $name, $param );
					if ( ! empty( $param['custom_desc'] ) ) {
						echo wp_kses_post( $param['custom_desc'] );
					}
					if ( ! empty( $param['desc'] ) ) {
						printf( '<p class="description">%s</p>', wp_kses_post( $param['desc'] ) );
					}
					do_action( 'wplwl_after_option_field', $name, $param );
					if ( empty( $param['not_wrap_html'] ) ) {
						echo wp_kses_post( '</td></tr>' );
					}
				}
			}
		}
		if ( isset( $options['section_end'] ) ) {
			if ( empty( $options['fields_html'] ) ) {
				echo wp_kses_post( '</table>' );
			}
			if ( ! empty( $options['section_end']['accordion'] ) ) {
				echo wp_kses_post( '</div></div>' );
			}
		}
	}

	public static function auto_color_arr() {
		return '{"#CF77CC":{"color":["#FD9FFF","#CB34C5","#E36BE1","#B735AA"],"pointer":"#F70707","palette":"#BA55D3"},"#F46E56":{"color":["#F9AA9B","#D83518","#FF927E","#B62E15"],"pointer":"#000000","palette":"#FF6347"},"#E5C516":{"color":["#FFF2A9","#D4B408","#FFEB80","#B69900"],"pointer":"#F70707","palette":"#F2CD04"},"#00907D":{"color":["#39CCB9","#0A7D6E","#1BAC99","#0A695D"],"pointer":"#F70707","palette":"#00907D"},"#5D9AD4":{"color":["#89C5FF","#0F6AC2","#52AAFF","#01509D"],"pointer":"#F70707","palette":"#1E90FF"},"#8E82DA":{"color":["#B6AAFF","#5E4AD9","#9C8BFF","#412EB4"],"pointer":"#F70707","palette":"#7B68EE"},"#E779B0":{"color":["#FFB8DB","#E42786","#FF85C2","#C5186E"],"pointer":"#F70707","palette":"#FF69B4"},"#FF3D00":{"color":["#FF9670","#D73B02","#FF6D36","#BC3503"],"pointer":"#000000","palette":"#FF4500"},"#F09E39":{"color":["#FFC073","#C76D00","#FFA231","#A65B00"],"pointer":"#F70707","palette":"#FF8C00"},"#5FB05F":{"color":["#75F875","#49B517","#57E757","#3E9912"],"pointer":"#F70707","palette":"#22B522"},"#4682B4":{"color":["#8BBFEB","#28679C","#5E9BCE","#1D5482"],"pointer":"#F70707","palette":"#4682B4"},"#FF8C00":{"color":["#F43415","#FC5508","#F19A01","#FEBD01","#FDE503","#CCEC21","#52CD4E","#22A8EB","#5476DA","#5F20B9","#9C28AC","#D02962"],"pointer":"#000000","palette":"linear-gradient(180deg, #5F20B9 0%, #D02962 22%, #FC5508 43.5%, #FDE503 61.5%, #52CD4E 81%, #22A8EB 100%)"},"#e23e57":{"color":["#ffcdd2","#e57373","#e53935","#b71c1c"]},"#8c82fc":{"color":["#e1bee7","#ba68c8","#8e24aa","#4a148c"]},"#521262":{"color":["#d1c4e9","#9575cd","#5e35b1","#311b92"]},"#3490de":{"color":["#bbdefb","#64b5f6","#1e88e5","#0d47a1"]},"#086972":{"color":["#b2dfdb","#4db6ac","#00897b","#004d40"]},"#36622b":{"color":["#c8e6c9","#81c784","#43a047","#1b5e20"]},"#729d39":{"color":["#f0f4c3","#dce775","#c0ca33","#827717"]},"#ffb400":{"color":["#fff9c4","#fff176","#fdd835","#f57f17"]},"#f08a5d":{"color":["#ffe0b2","#ffb74d","#fb8c00","#e65100"]},"#393232":{"color":["#d7ccc8","#a1887f","#6d4c41","#3e2723"]},"#52616b":{"color":["#cfd8dc","#90a4ae","#546e7a","#263238"]},"#f67280":{"color":["#e6194b","#3cb44b","#ffe119","#0082c8","#f58231","#911eb4","#46f0f0","#f032e6","#d2f53c","#fabebe","#008080","#e6beff","#aa6e28","#fffac8","#800000","#aaffc3","#808000","#ffd8b1","#000080","#808080","#FFFFFF","#000000"]}}';
	}


	public static function get_random_color() {
		$color_arr = json_decode(self::auto_color_arr(),true);
		$colors_array = array_column(array_values($color_arr),'color');
		$index        = wp_rand( 0, count($colors_array) - 1 );
		$colors       = $colors_array[ $index ];
		$slices       = self::get_instance()->get_params( 'wheel', 'bg_color' );
		$count_colors = count($colors);
		$count_slides = count($slices);
		if ($count_slides > $count_colors){
			$result = [];
			$j = 0;
			for ($i = 0; $i < $count_slides; $i++){
				if ($j === $count_colors){
					$j = 0;
				}
				$result[] = $colors[$j];
				$j++;
			}
		}else{
			$result = array_slice( $colors, 0, count( $slices ) );
		}
		return $result;
	}

	public static function remove_other_script() {
		global $wp_scripts;
		$scripts         = $wp_scripts->registered;
		$exclude_dequeue = apply_filters( 'viwplwl_exclude_dequeue_scripts', array(
			'dokan-vue-bootstrap',
			'query-monitor',
			'uip-app',
			'uip-vue',
			'uip-toolbar-app'
		) );
		foreach ( $scripts as $script ) {
			if ( in_array( $script->handle, $exclude_dequeue ) ) {
				continue;
			}
			preg_match( '/\/wp-/i', $script->src, $result );
			if ( count( array_filter( $result ) ) ) {
				preg_match( '/(\/wp-content\/plugins|\/wp-content\/themes)/i', $script->src, $result1 );
				if ( count( array_filter( $result1 ) ) ) {
					wp_dequeue_script( $script->handle );
				}
			} else {
				wp_dequeue_script( $script->handle );
			}
		}
		wp_dequeue_script( 'select-js' );//Causes select2 error, from ThemeHunk MegaMenu Plus plugin
		wp_dequeue_style( 'eopa-admin-css' );
	}

	public static function enqueue_style( $handles = array(), $srcs = array(), $is_suffix = array(), $des = array(), $type = 'enqueue' ) {
		if ( empty( $handles ) || empty( $srcs ) ) {
			return;
		}
		$action = $type === 'enqueue' ? 'wp_enqueue_style' : 'wp_register_style';
		$suffix = WP_DEBUG ? '' : '.min';
		foreach ( $handles as $i => $handle ) {
			if ( ! $handle || empty( $srcs[ $i ] ) ) {
				continue;
			}
			$suffix_t = ! empty( $is_suffix[ $i ] ) ? '.min' : $suffix;
			$action( $handle, VI_WORDPRESS_LUCKY_WHEEL_CSS . $srcs[ $i ] . $suffix_t . '.css', ! empty( $des[ $i ] ) ? $des[ $i ] : array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
		}
	}

	public static function enqueue_script( $handles = array(), $srcs = array(), $is_suffix = array(), $des = array(), $type = 'enqueue', $in_footer = false ) {
		if ( empty( $handles ) || empty( $srcs ) ) {
			return;
		}
		$action = $type === 'register' ? 'wp_register_script' : 'wp_enqueue_script';
		$suffix = WP_DEBUG ? '' : '.min';
		foreach ( $handles as $i => $handle ) {
			if ( ! $handle || empty( $srcs[ $i ] ) ) {
				continue;
			}
			$suffix_t = ! empty( $is_suffix[ $i ] ) ? '.min' : $suffix;
			$action( $handle, VI_WORDPRESS_LUCKY_WHEEL_JS . $srcs[ $i ] . $suffix_t . '.js', ! empty( $des[ $i ] ) ? $des[ $i ] : array( 'jquery' ),
				VI_WORDPRESS_LUCKY_WHEEL_VERSION, $in_footer );
		}
	}

}