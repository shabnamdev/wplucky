<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WORDPRESS_LUCKY_WHEEL_Frontend_Mobile {
	protected $settings;
	protected $mobile_settings;
	protected $table;
	protected $notifier;
	protected $language = '';
	protected $shortcode_rendered = false;

	public function __construct() {
		$this->settings        = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance();
		$this->mobile_settings = WPLWL_Mobile_Settings::get_instance();
		$this->table           = WPLWL_Mobile_Table::get_instance();
		$this->notifier        = new WPLWL_Mobile_Notifier();

		add_action( 'init', array( 'WPLWL_Mobile_Table', 'maybe_create_table' ), 5 );
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_ajax_wplwl_mobile_spin', array( $this, 'spin' ) );
		add_action( 'wp_ajax_nopriv_wplwl_mobile_spin', array( $this, 'spin' ) );
		add_action( 'wp_ajax_wplwl_mobile_send_otp', array( $this, 'send_otp' ) );
		add_action( 'wp_ajax_nopriv_wplwl_mobile_send_otp', array( $this, 'send_otp' ) );
		add_action( 'wp_ajax_wplwl_mobile_verify_otp', array( $this, 'verify_otp' ) );
		add_action( 'wp_ajax_nopriv_wplwl_mobile_verify_otp', array( $this, 'verify_otp' ) );
		add_action( 'wplwl_reset_total_spins', array( $this->table, 'reset_spin_counts' ), 20 );
		add_filter( 'wplwl_disable_email_popup', array( $this, 'suppress_email_popup' ) );
	}

	public function register_shortcode() {
		add_shortcode( 'wordpress_lucky_wheel_mobile', array( $this, 'shortcode' ) );
	}

	public function suppress_email_popup( $disable ) {
		if ( $disable || 'on' !== $this->mobile_settings->get( 'suppress_email_popup' ) ) {
			return $disable;
		}
		if ( $this->shortcode_rendered ) {
			return true;
		}
		if ( ! is_singular() ) {
			return $disable;
		}
		global $post;
		if ( $post instanceof WP_Post && has_shortcode( $post->post_content, 'wordpress_lucky_wheel_mobile' ) ) {
			return true;
		}
		return $disable;
	}

	public function register_assets() {
		 
		 
		if ( ! wp_style_is( 'wordpress-lucky-wheel-shortcode', 'registered' ) ) {
			wp_register_style( 'wordpress-lucky-wheel-shortcode', VI_WORDPRESS_LUCKY_WHEEL_CSS . 'shortcode.css', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
		}
		if ( ! wp_style_is( 'wplwl-mobile-shortcode', 'registered' ) ) {
			wp_register_style( 'wplwl-mobile-shortcode', VI_WORDPRESS_LUCKY_WHEEL_CSS . 'mobile-shortcode.css', array( 'wordpress-lucky-wheel-shortcode' ), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
		}
		if ( ! wp_script_is( 'wplwl-mobile-shortcode', 'registered' ) ) {
			wp_register_script( 'wplwl-mobile-shortcode', VI_WORDPRESS_LUCKY_WHEEL_JS . 'mobile-shortcode.js', array( 'jquery' ), VI_WORDPRESS_LUCKY_WHEEL_VERSION, true );
		}
	}

	protected function detect_language() {
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$default = apply_filters( 'wpml_default_language', null );
			$current = apply_filters( 'wpml_current_language', null );
			if ( $current && $current !== $default ) {
				$this->language = $current;
			}
		} elseif ( class_exists( 'Polylang' ) && function_exists( 'pll_current_language' ) ) {
			$default = pll_default_language( 'slug' );
			$current = pll_current_language( 'slug' );
			if ( $current && $current !== $default ) {
				$this->language = $current;
			}
		}
	}

	protected function prefilled_mobile() {
		$mobile = '';
		if ( 'on' === $this->mobile_settings->get( 'prefill_billing_phone' ) && is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$mobile  = get_user_meta( $user_id, 'billing_phone', true );
			if ( ! $mobile ) {
				$mobile = get_user_meta( $user_id, 'mobile', true );
			}
		}
		$mobile = apply_filters( 'wplwl_mobile_prefill', $mobile, get_current_user_id() );
		$mobile = WPLWL_Mobile_Validator::national( $mobile );
		return $mobile;
	}

	public function shortcode( $atts ) {
		if ( 'on' !== $this->mobile_settings->get( 'enable' ) || 'on' !== $this->settings->get_params( 'general', 'enable' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return '<div class="wplwl-mobile-admin-notice">نسخه موبایلی چرخ شانس غیرفعال است. برای فعال‌سازی، به تب «ماژول موبایل» در تنظیمات افزونه بروید.</div>';
			}
			return '';
		}

		$this->shortcode_rendered = true;
		$this->detect_language();
		$atts = shortcode_atts( array(
			'bg_image'             => $this->settings->get_params( 'wheel_wrap', 'bg_image' ),
			'bg_color'             => $this->settings->get_params( 'wheel_wrap', 'bg_color' ),
			'text_color'           => $this->settings->get_params( 'wheel_wrap', 'text_color' ),
			'pointer_color'        => $this->settings->get_params( 'wheel_wrap', 'pointer_color' ),
			'spin_button_color'    => $this->settings->get_params( 'wheel_wrap', 'spin_button_color' ),
			'spin_button_bg_color' => $this->settings->get_params( 'wheel_wrap', 'spin_button_bg_color' ),
			'pointer_position'     => $this->settings->get_params( 'wheel_wrap', 'pointer_position' ),
			'class'                => '',
		), $atts, 'wordpress_lucky_wheel_mobile' );

		$wheel = WPLWL_Wheel_Engine::prepare_wheel( $this->settings->get_params( 'wheel' ) );
		if ( empty( $wheel['prize_type'] ) || array_sum( array_map( 'absint', $wheel['probability'] ) ) < 1 ) {
			return current_user_can( 'manage_options' ) ? '<div class="wplwl-mobile-admin-notice">برای نمایش گردونه، ابتدا جایزه‌ها و احتمال هر بخش را در تنظیمات اصلی تکمیل کنید.</div>' : '';
		}

		$labels          = $this->settings->get_params( 'wheel', 'custom_label', $this->language );
		$quantity_label  = $this->settings->get_params( 'wheel', 'quantity_label', $this->language );
		$display_labels  = array();
		$available_total = 0;
		foreach ( $wheel['prize_type'] as $index => $type ) {
			$label         = isset( $labels[ $index ] ) ? $labels[ $index ] : '';
			$quantity_text = '';
			$quantity      = isset( $wheel['prize_quantity'][ $index ] ) ? (int) $wheel['prize_quantity'][ $index ] : -1;
			$probability   = isset( $wheel['probability'][ $index ] ) ? absint( $wheel['probability'][ $index ] ) : 0;
			if ( 'non' === $type || 0 !== $quantity ) {
				$available_total += $probability;
			}
			if ( 'non' !== $type && $quantity > 0 ) {
				$quantity_text = str_replace( '{prize_quantity}', $quantity, $quantity_label );
			}
			$label = str_replace( '{quantity_label}', $quantity_text, $label );
			$label = str_replace( '{prize_value}', '', $label );
			$display_labels[] = $label;
		}
		if ( $available_total < 1 ) {
			return '';
		}

		 
		 
		$this->register_assets();
		wp_enqueue_style( 'wordpress-lucky-wheel-shortcode' );
		wp_enqueue_style( 'wplwl-mobile-shortcode' );
		wp_enqueue_script( 'wplwl-mobile-shortcode' );
		if ( 'firework' === $this->settings->get_params( 'wheel_wrap', 'congratulations_effect' ) ) {
			wp_enqueue_style( 'wordpress-lucky-wheel-frontend-style-firework', VI_WORDPRESS_LUCKY_WHEEL_CSS . 'firework.css', array(), VI_WORDPRESS_LUCKY_WHEEL_VERSION );
		}

		global $wplwl_mobile_shortcode_id;
		$wplwl_mobile_shortcode_id = null === $wplwl_mobile_shortcode_id ? 1 : $wplwl_mobile_shortcode_id + 1;
		$id                        = 'wordpress-lucky-wheel-mobile-shortcode-' . absint( $wplwl_mobile_shortcode_id );
		$selector                  = '#' . $id . '.wp-lucky-wheel-shortcode-container';
		$pointer_position          = sanitize_key( $atts['pointer_position'] );
		if ( 'random' === $pointer_position ) {
			$positions        = array( 'center', 'top', 'right', 'bottom' );
			$pointer_position = $positions[ wp_rand( 0, 3 ) ];
		}
		if ( ! in_array( $pointer_position, array( 'center', 'top', 'right', 'bottom' ), true ) ) {
			$pointer_position = 'center';
		}

		$colors      = 'on' === $this->settings->get_params( 'wheel', 'random_color' ) ? VI_WORDPRESS_LUCKY_WHEEL_DATA::get_random_color() : ( isset( $wheel['bg_color'] ) ? $wheel['bg_color'] : array() );
		$text_colors = isset( $wheel['slices_text_color'] ) && is_array( $wheel['slices_text_color'] ) ? $wheel['slices_text_color'] : array_fill( 0, count( $wheel['prize_type'] ), '#ffffff' );
		$args        = array(
			'ajaxurl'                => admin_url( 'admin-ajax.php' ),
			'nonce'                  => wp_create_nonce( 'wplwl_mobile_frontend' ),
			'language'               => $this->language,
			'pointer_position'       => $pointer_position,
			'color'                  => $colors,
			'slices_text_color'      => $text_colors,
			'label'                  => $display_labels,
			'prize_type'             => $wheel['prize_type'],
			'spinning_time'          => max( 1, absint( $this->settings->get_params( 'wheel', 'spinning_time' ) ) ),
			'wheel_speed'            => max( 1, absint( $this->settings->get_params( 'wheel', 'wheel_speed' ) ) ),
			'font_size'              => absint( $this->settings->get_params( 'wheel', 'font_size' ) ),
			'wheel_size'             => absint( $this->settings->get_params( 'wheel', 'wheel_size' ) ),
			'wheel_center_color'     => $this->settings->get_params( 'wheel_wrap', 'wheel_center_color' ),
			'wheel_border_color'     => $this->settings->get_params( 'wheel_wrap', 'wheel_border_color' ),
			'wheel_dot_color'        => $this->settings->get_params( 'wheel_wrap', 'wheel_dot_color' ),
			'center_image'           => wp_get_attachment_url( $this->settings->get_params( 'wheel_wrap', 'wheel_center_image' ) ),
			'gdpr'                   => $this->settings->get_params( 'wheel_wrap', 'gdpr' ),
			'gdpr_warning'           => 'برای شرکت در چرخ شانس، ابتدا با قوانین آن موافقت کنید.',
			'name_enable'            => $this->mobile_settings->get( 'name_enable' ),
			'name_required'          => $this->mobile_settings->get( 'name_required' ),
			'otp_enable'             => $this->mobile_settings->get( 'otp_enable' ),
			'marketing_enable'       => $this->mobile_settings->get( 'marketing_consent_enable' ),
			'congratulations_effect' => $this->settings->get_params( 'wheel_wrap', 'congratulations_effect' ),
			'messages'               => array(
				'mobile_required' => 'لطفاً شماره موبایل خود را وارد کنید.',
				'mobile_invalid'  => 'شماره موبایل واردشده معتبر نیست؛ برای نمونه: 09121234567',
				'name_required'   => 'لطفاً نام و نام خانوادگی خود را وارد کنید.',
				'otp_required'    => 'پیش از چرخاندن گردونه، شماره موبایل خود را با رمز یک‌بارمصرف تأیید کنید.',
				'otp_sent'        => 'رمز یک‌بارمصرف برای شما ارسال شد.',
				'network_error'   => 'ارتباط با سایت برقرار نشد. چند لحظه بعد دوباره تلاش کنید.',
			),
		);

		$background = $atts['bg_image'];
		if ( $background && is_numeric( $background ) ) {
			$background = wp_get_attachment_url( absint( $background ) );
		}
		$css = $selector . '{';
		if ( $background ) {
			$css .= 'background-image:url("' . esc_url_raw( $background ) . '");';
		}
		if ( $atts['bg_color'] ) {
			$css .= 'background-color:' . $atts['bg_color'] . ';';
		}
		if ( $atts['text_color'] ) {
			$css .= 'color:' . $atts['text_color'] . ';';
		}
		$css .= '}';
		if ( $atts['pointer_color'] ) {
			$css .= $selector . ' .wp-lucky-wheel-shortcode-wheel-pointer:before{color:' . $atts['pointer_color'] . ';}';
			$css .= '@media(max-width:640px){' . $selector . ' .wp-lucky-wheel-shortcode-wheel-pointer:before{color:' . $atts['pointer_color'] . ';background-color:' . $atts['pointer_color'] . ';}}';
		}
		$css .= $selector . ' .wp-lucky-wheel-shortcode-wheel-button-wrap{';
		if ( $atts['spin_button_color'] ) {
			$css .= 'color:' . $atts['spin_button_color'] . ';';
		}
		if ( $atts['spin_button_bg_color'] ) {
			$css .= 'background-color:' . $atts['spin_button_bg_color'] . ';';
		}
		$css .= '}';
		wp_add_inline_style( 'wplwl-mobile-shortcode', $css );

		$classes = array(
			'wp-lucky-wheel-shortcode-container',
			'wp-lucky-wheel-shortcode-pointer-position-' . $pointer_position,
			'wplwl-mobile-shortcode-container',
			'wplwl-mobile-shortcode-pointer-position-' . $pointer_position,
		);
		if ( 'center' !== $pointer_position ) {
			$classes[] = 'wp-lucky-wheel-shortcode-margin-position';
			$classes[] = 'wplwl-mobile-shortcode-margin-position';
		}
		if ( $atts['class'] ) {
			foreach ( preg_split( '/\s+/', $atts['class'] ) as $custom_class ) {
				$custom_class = sanitize_html_class( $custom_class );
				if ( $custom_class ) {
					$classes[] = $custom_class;
				}
			}
		}

		$gdpr_message = $this->settings->get_params( 'wheel_wrap', 'gdpr_message', $this->language );
		if ( ! $gdpr_message ) {
			$gdpr_message = 'با قوانین شرکت در چرخ شانس موافقم.';
		}
		$prefill = $this->prefilled_mobile();
		ob_start();
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>" data-mobile_args="<?php echo esc_attr( wp_json_encode( $args ) ); ?>" dir="rtl">
			<div class="wp-lucky-wheel-shortcode-wheel-container wplwl-mobile-shortcode-wheel-container">
				<div class="wp-lucky-wheel-shortcode-wheel-canvas wplwl-mobile-shortcode-wheel-canvas">
					<canvas class="wp-lucky-wheel-shortcode-wheel-canvas-1 wplwl-mobile-shortcode-wheel-canvas-1"></canvas>
					<canvas class="wp-lucky-wheel-shortcode-wheel-canvas-2 wplwl-mobile-shortcode-wheel-canvas-2"></canvas>
					<canvas class="wp-lucky-wheel-shortcode-wheel-canvas-3 wplwl-mobile-shortcode-wheel-canvas-3"></canvas>
					<div class="wp-lucky-wheel-shortcode-wheel-pointer-container wplwl-mobile-shortcode-wheel-pointer-container">
						<div class="wp-lucky-wheel-shortcode-wheel-pointer-before"></div>
						<div class="wp-lucky-wheel-shortcode-wheel-pointer-main wplwl-mobile-shortcode-wheel-pointer-main">
							<span class="wplwl-location wp-lucky-wheel-shortcode-wheel-pointer wp-lucky-wheel-shortcode-wheel-pointer-<?php echo esc_attr( $pointer_position ); ?> wplwl-mobile-shortcode-wheel-pointer wplwl-mobile-shortcode-wheel-pointer-<?php echo esc_attr( $pointer_position ); ?>"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="wp-lucky-wheel-shortcode-content-container wplwl-mobile-shortcode-content-container">
				<div class="wp-lucky-wheel-shortcode-wheel-description wplwl-mobile-shortcode-wheel-description"><?php echo do_shortcode( $this->settings->get_params( 'wheel_wrap', 'description', $this->language ) );  ?></div>
				<div class="wplwl-congratulations-effect"><div class="wplwl-congratulations-effect-before"></div><div class="wplwl-congratulations-effect-after"></div></div>
				<div class="wp-lucky-wheel-shortcode-wheel-fields-container wplwl-mobile-shortcode-wheel-fields-container">
					<?php if ( 'on' === $this->mobile_settings->get( 'name_enable' ) ) : ?>
						<div class="wp-lucky-wheel-shortcode-wheel-field-name-wrap wp-lucky-wheel-shortcode-wheel-field-wrap wplwl-mobile-shortcode-wheel-field-name-wrap wplwl-mobile-shortcode-wheel-field-wrap">
							<input type="text" autocomplete="name" class="wp-lucky-wheel-shortcode-wheel-field-name wp-lucky-wheel-shortcode-wheel-field wplwl-mobile-shortcode-wheel-field-name wplwl-mobile-shortcode-wheel-field" placeholder="نام و نام خانوادگی">
						</div>
					<?php endif; ?>
					<div class="wp-lucky-wheel-shortcode-wheel-field-mobile-wrap wp-lucky-wheel-shortcode-wheel-field-wrap wplwl-mobile-shortcode-wheel-field-mobile-wrap wplwl-mobile-shortcode-wheel-field-wrap">
						<span class="wp-lucky-wheel-shortcode-wheel-field-error wp-lucky-wheel-shortcode-wheel-field-error-mobile wplwl-mobile-shortcode-wheel-field-error-mobile wplwl-mobile-shortcode-wheel-field-error"></span>
						<input type="tel" inputmode="numeric" autocomplete="tel" dir="ltr" value="<?php echo esc_attr( $prefill ); ?>" class="wp-lucky-wheel-shortcode-wheel-field-mobile wp-lucky-wheel-shortcode-wheel-field wplwl-mobile-shortcode-wheel-field-mobile wplwl-mobile-shortcode-wheel-field" placeholder="شماره موبایل؛ مانند 09121234567">
					</div>
					<input type="text" name="company_website" value="" tabindex="-1" autocomplete="off" class="wplwl-mobile-honeypot" aria-hidden="true">
					<?php if ( 'on' === $this->mobile_settings->get( 'otp_enable' ) ) : ?>
						<div class="wplwl-mobile-shortcode-otp-actions"><button type="button" class="wplwl-mobile-shortcode-send-otp">ارسال رمز یک‌بارمصرف</button></div>
						<div class="wplwl-mobile-shortcode-otp-wrap" hidden>
							<input type="text" inputmode="numeric" maxlength="8" class="wp-lucky-wheel-shortcode-wheel-field wplwl-mobile-shortcode-otp-code wplwl-mobile-shortcode-wheel-field" placeholder="رمز یک‌بارمصرف">
							<button type="button" class="wplwl-mobile-shortcode-verify-otp">تأیید شماره موبایل</button>
						</div>
						<div class="wplwl-mobile-shortcode-otp-status" aria-live="polite"></div>
					<?php endif; ?>
					<div class="wp-lucky-wheel-shortcode-wheel-button-wrap wplwl-mobile-shortcode-wheel-button-wrap">
						<span role="button" tabindex="0" class="wp-lucky-wheel-shortcode-wheel-button wplwl-mobile-shortcode-wheel-button"><?php echo wp_kses_post( $this->settings->get_params( 'wheel_wrap', 'spin_button', $this->language ) ); ?></span>
					</div>
					<?php if ( 'on' === $this->settings->get_params( 'wheel_wrap', 'gdpr' ) ) : ?>
						<label class="wp-lucky-wheel-shortcode-wheel-gdpr-wrap wplwl-mobile-shortcode-wheel-gdpr-wrap"><input type="checkbox" class="wplwl-mobile-terms"> <span><?php echo wp_kses_post( $gdpr_message ); ?></span></label>
					<?php endif; ?>
					<?php if ( 'on' === $this->mobile_settings->get( 'marketing_consent_enable' ) ) : ?>
						<label class="wp-lucky-wheel-shortcode-wheel-gdpr-wrap wplwl-mobile-shortcode-wheel-gdpr-wrap"><input type="checkbox" class="wplwl-mobile-marketing-consent"> <span><?php echo wp_kses_post( $this->mobile_settings->get( 'marketing_consent_text' ) ); ?></span></label>
					<?php endif; ?>
				</div>
			</div>
			<div class="wp-lucky-wheel-shortcode-result-container wplwl-mobile-shortcode-result-container" aria-live="polite"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	protected function verify_frontend_request() {
		if ( ! check_ajax_referer( 'wplwl_mobile_frontend', 'nonce', false ) ) {
			wp_send_json( array( 'allow_spin' => 'اعتبار درخواست پایان یافته است. صفحه را تازه‌سازی و دوباره تلاش کنید.' ), 403 );
		}
		if ( 'on' !== $this->mobile_settings->get( 'enable' ) || 'on' !== $this->settings->get_params( 'general', 'enable' ) ) {
			wp_send_json( array( 'allow_spin' => 'در حال حاضر امکان استفاده از نسخه موبایلی چرخ شانس وجود ندارد.' ), 403 );
		}
		if ( 'on' === $this->mobile_settings->get( 'honeypot_enable' ) && ! empty( $_POST['company_website'] ) ) {
			wp_send_json( array( 'allow_spin' => 'درخواست نامعتبر است.' ), 400 );
		}
	}

	protected function request_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	}

	protected function normalized_request_mobile() {
		$raw = isset( $_POST['user_mobile'] ) ? sanitize_text_field( wp_unslash( $_POST['user_mobile'] ) ) : '';
		return WPLWL_Mobile_Validator::normalize( $raw );
	}

	protected function request_campaign() {
		 
		 
		return 'default';
	}

	protected function prize_type_label( $type ) {
		switch ( sanitize_key( $type ) ) {
			case 'custom':
				return 'جایزه سفارشی';
			case 'non':
				return 'بدون جایزه';
			default:
				return 'جایزه';
		}
	}

	protected function prize_display_label( $prize ) {
		$label = isset( $prize['prize_label'] ) ? trim( wp_strip_all_tags( $prize['prize_label'] ) ) : '';
		if ( '' === $label && isset( $prize['prize_type'] ) && 'non' !== $prize['prize_type'] ) {
			$label = $this->prize_type_label( $prize['prize_type'] );
		}
		return $label;
	}

	public function send_otp() {
		$this->verify_frontend_request();
		if ( 'on' !== $this->mobile_settings->get( 'otp_enable' ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'تأیید شماره موبایل با رمز یک‌بارمصرف غیرفعال است.' ) );
		}
		if ( ! WPLWL_Mobile_Notifier::is_pwoosms_available() ) {
			wp_send_json( array( 'success' => false, 'message' => 'برای ارسال رمز یک‌بارمصرف، ابتدا افزونه «پیامک ووکامرس فارسی» را فعال و درگاه آن را تنظیم کنید.' ) );
		}
		$mobile   = $this->normalized_request_mobile();
		$campaign = $this->request_campaign();
		if ( ! WPLWL_Mobile_Validator::is_valid_iran_mobile( $mobile ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'شماره موبایل واردشده معتبر نیست. نمونه صحیح: 09121234567' ) );
		}
		$rate_key = 'wplwl_otp_rate_' . md5( $mobile . '|' . $this->request_ip() );
		if ( get_transient( $rate_key ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'رمز یک‌بارمصرف به‌تازگی ارسال شده است. لطفاً کمی صبر کنید و سپس دوباره درخواست دهید.' ) );
		}
		$length = absint( $this->mobile_settings->get( 'otp_length', 5 ) );
		$max    = (int) pow( 10, $length ) - 1;
		$otp    = str_pad( (string) wp_rand( 0, $max ), $length, '0', STR_PAD_LEFT );
		$key    = 'wplwl_otp_' . md5( $mobile . '|' . $campaign );
		$expiry = absint( $this->mobile_settings->get( 'otp_expiry', 3 ) ) * MINUTE_IN_SECONDS;
		set_transient( $key, array(
			'hash' => wp_hash_password( $otp ), 'mobile' => $mobile, 'campaign' => $campaign,
			'expires' => time() + $expiry, 'attempts' => 0,
		), $expiry );
		set_transient( $rate_key, 1, absint( $this->mobile_settings->get( 'otp_resend_delay', 60 ) ) );

		$message = WPLWL_Mobile_Notifier::replace_tags( $this->mobile_settings->get( 'otp_message' ), array(
			'otp' => $otp, 'site_title' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), 'mobile' => $mobile,
		) );
		$result = $this->notifier->send( $mobile, $message, array( 'type' => 'otp', 'campaign' => $campaign ) );
		if ( is_wp_error( $result ) ) {
			delete_transient( $key );
			delete_transient( $rate_key );
			wp_send_json( array( 'success' => false, 'message' => $result->get_error_message() ) );
		}
		wp_send_json( array( 'success' => true, 'message' => 'رمز یک‌بارمصرف برای شماره شما ارسال شد.' ) );
	}

	public function verify_otp() {
		$this->verify_frontend_request();
		$mobile   = $this->normalized_request_mobile();
		$campaign = $this->request_campaign();
		$code     = isset( $_POST['otp_code'] ) ? WPLWL_Mobile_Validator::convert_digits( sanitize_text_field( wp_unslash( $_POST['otp_code'] ) ) ) : '';
		$key      = 'wplwl_otp_' . md5( $mobile . '|' . $campaign );
		$data     = get_transient( $key );
		if ( ! is_array( $data ) || empty( $data['hash'] ) || time() > absint( $data['expires'] ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'زمان استفاده از این رمز به پایان رسیده است. رمز تازه‌ای درخواست کنید.' ) );
		}
		$data['attempts'] = isset( $data['attempts'] ) ? absint( $data['attempts'] ) + 1 : 1;
		if ( $data['attempts'] > 5 ) {
			delete_transient( $key );
			wp_send_json( array( 'success' => false, 'message' => 'تعداد تلاش‌های ناموفق بیش از حد مجاز بود. لطفاً رمز تازه‌ای درخواست کنید.' ) );
		}
		set_transient( $key, $data, max( 1, absint( $data['expires'] ) - time() ) );
		if ( ! wp_check_password( $code, $data['hash'] ) ) {
			wp_send_json( array( 'success' => false, 'message' => 'رمز واردشده صحیح نیست.' ) );
		}
		$token = wp_generate_password( 32, false, false );
		set_transient( 'wplwl_verified_' . md5( $token ), array( 'mobile' => $mobile, 'campaign' => $campaign ), 15 * MINUTE_IN_SECONDS );
		delete_transient( $key );
		wp_send_json( array( 'success' => true, 'message' => 'شماره موبایل شما با موفقیت تأیید شد.', 'token' => $token ) );
	}

	protected function get_spin_limit() {
		$limit = absint( $this->mobile_settings->get( 'spin_num', 0 ) );
		return $limit > 0 ? $limit : max( 1, absint( $this->settings->get_params( 'general', 'spin_num' ) ) );
	}

	protected function get_delay_seconds() {
		$unit  = $this->mobile_settings->get( 'delay_unit', 'inherit' );
		$delay = absint( $this->mobile_settings->get( 'delay', 0 ) );
		if ( 'inherit' === $unit ) {
			$unit  = $this->settings->get_params( 'general', 'delay_unit' );
			$delay = absint( $this->settings->get_params( 'general', 'delay' ) );
		}
		switch ( $unit ) {
			case 'm': return $delay * MINUTE_IN_SECONDS;
			case 'h': return $delay * HOUR_IN_SECONDS;
			case 'd': return $delay * DAY_IN_SECONDS;
			default: return $delay;
		}
	}

	protected function wait_message( $seconds ) {
		$seconds = max( 1, absint( $seconds ) );
		$days    = floor( $seconds / DAY_IN_SECONDS );
		$hours   = floor( ( $seconds % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
		$minutes = floor( ( $seconds % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
		$secs    = $seconds % MINUTE_IN_SECONDS;
		$parts   = array();
		if ( $days ) { $parts[] = $days . ' روز'; }
		if ( $hours ) { $parts[] = $hours . ' ساعت'; }
		if ( $minutes ) { $parts[] = $minutes . ' دقیقه'; }
		if ( $secs && ! $days ) { $parts[] = $secs . ' ثانیه'; }
		return 'برای چرخاندن دوباره گردونه، لازم است ' . implode( ' و ', $parts ) . ' دیگر صبر کنید.';
	}

	protected function verify_spin_token( $mobile, $campaign ) {
		if ( 'on' !== $this->mobile_settings->get( 'otp_enable' ) ) {
			return true;
		}
		$token = isset( $_POST['verification_token'] ) ? sanitize_text_field( wp_unslash( $_POST['verification_token'] ) ) : '';
		$data  = $token ? get_transient( 'wplwl_verified_' . md5( $token ) ) : false;
		if ( ! is_array( $data ) || ! isset( $data['mobile'], $data['campaign'] ) || $mobile !== $data['mobile'] || $campaign !== $data['campaign'] ) {
			return false;
		}
		return true;
	}

	public function spin() {
		$this->verify_frontend_request();
		$mobile   = $this->normalized_request_mobile();
		$campaign = $this->request_campaign();
		$name     = isset( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : '';
		if ( ! WPLWL_Mobile_Validator::is_valid_iran_mobile( $mobile ) ) {
			wp_send_json( array( 'allow_spin' => 'شماره موبایل واردشده معتبر نیست. نمونه صحیح: 09121234567' ) );
		}
		if ( 'on' === $this->mobile_settings->get( 'name_enable' ) && 'on' === $this->mobile_settings->get( 'name_required' ) && ! $name ) {
			wp_send_json( array( 'allow_spin' => 'لطفاً نام و نام خانوادگی خود را وارد کنید.' ) );
		}
		if ( ! $this->verify_spin_token( $mobile, $campaign ) ) {
			wp_send_json( array( 'allow_spin' => 'پیش از چرخاندن گردونه، شماره موبایل خود را با رمز یک‌بارمصرف تأیید کنید.', 'otp_required' => true ) );
		}

		$rate_key = 'wplwl_mobile_spin_rate_' . md5( $mobile . '|' . $this->request_ip() );
		if ( get_transient( $rate_key ) ) {
			wp_send_json( array( 'allow_spin' => 'درخواست‌ها با فاصله بسیار کوتاه ارسال شده‌اند. چند ثانیه صبر کنید و دوباره تلاش کنید.' ) );
		}
		set_transient( $rate_key, 1, absint( $this->mobile_settings->get( 'rate_limit_seconds', 3 ) ) );

		$row   = $this->table->get_row( $mobile, $campaign );
		$now   = time();
		$limit = $this->get_spin_limit();
		$delay = $this->get_delay_seconds();
		if ( $row ) {
			if ( empty( $row['active'] ) ) {
				wp_send_json( array( 'allow_spin' => 'امکان شرکت این شماره موبایل در چرخ شانس غیرفعال شده است.' ) );
			}
			if ( absint( $row['spin_num'] ) >= $limit ) {
				wp_send_json( array( 'allow_spin' => 'تعداد دفعات مجاز چرخش برای این شماره موبایل به پایان رسیده است.' ) );
			}
			if ( $delay > 0 && ( $now - absint( $row['last_spin'] ) ) < $delay ) {
				wp_send_json( array( 'allow_spin' => $this->wait_message( $delay + absint( $row['last_spin'] ) - $now ) ) );
			}
		}

		$language = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
		$prize    = WPLWL_Wheel_Engine::spin( $language );
		if ( is_wp_error( $prize ) ) {
			wp_send_json( array( 'allow_spin' => $prize->get_error_message() ) );
		}

		$marketing = ! empty( $_POST['marketing_consent'] ) ? 1 : 0;
		$verified  = 'on' === $this->mobile_settings->get( 'otp_enable' ) ? 1 : 0;
		$coupons   = $row && ! empty( $row['mobile_coupons'] ) ? maybe_unserialize( $row['mobile_coupons'] ) : array();
		$labels    = $row && ! empty( $row['mobile_labels'] ) ? maybe_unserialize( $row['mobile_labels'] ) : array();
		$prizes    = $row && ! empty( $row['mobile_prizes'] ) ? maybe_unserialize( $row['mobile_prizes'] ) : array();
		$coupons   = is_array( $coupons ) ? $coupons : array();
		$labels    = is_array( $labels ) ? $labels : array();
		$prizes    = is_array( $prizes ) ? $prizes : array();
		if ( 'win' === $prize['result'] ) {
			$display_label = $this->prize_display_label( $prize );
			$coupons[]     = sanitize_text_field( $prize['prize_value'] );
			$labels[]      = $display_label;
			$prizes[]      = array(
				'type'       => sanitize_key( $prize['prize_type'] ),
				'type_label' => $this->prize_type_label( $prize['prize_type'] ),
				'label'      => $display_label,
				'value'      => sanitize_text_field( $prize['prize_value'] ),
				'won_at'     => $now,
			);
		}
		if ( $row ) {
			$this->table->update_by_id( $row['id'], array(
				'customer_name' => $name ? $name : $row['customer_name'],
				'spin_num' => absint( $row['spin_num'] ) + 1,
				'total_spins' => absint( $row['total_spins'] ) + 1,
				'last_spin' => $now, 'date_updated' => $now,
				'mobile_coupons' => $coupons, 'mobile_labels' => $labels, 'mobile_prizes' => $prizes,
				'verified' => $verified ? 1 : absint( $row['verified'] ),
				'verified_at' => $verified ? $now : absint( $row['verified_at'] ),
				'marketing_consent' => $marketing,
				'user_id' => get_current_user_id(),
			) );
		} else {
			$this->table->insert( array(
				'mobile_e164' => $mobile, 'mobile_national' => WPLWL_Mobile_Validator::national( $mobile ),
				'customer_name' => $name, 'campaign_key' => $campaign, 'spin_num' => 1, 'total_spins' => 1,
				'last_spin' => $now, 'date_created' => $now, 'date_updated' => $now,
				'mobile_coupons' => $coupons, 'mobile_labels' => $labels, 'mobile_prizes' => $prizes, 'verified' => $verified,
				'verified_at' => $verified ? $now : 0, 'marketing_consent' => $marketing,
				'user_id' => get_current_user_id(), 'active' => 1,
			) );
		}

		$prize_display_label   = $this->prize_display_label( $prize );
		$prize_value_display   = '' !== trim( (string) $prize['prize_value'] ) ? $prize['prize_value'] : 'بدون کد';
		$notification_template = 'win' === $prize['result'] ? $this->mobile_settings->get( 'win_notification' ) : $this->mobile_settings->get( 'lost_notification' );
		$tags = array(
			'prize_label' => '<strong>' . esc_html( $prize_display_label ) . '</strong>',
			'prize_value' => '<strong>' . esc_html( $prize_value_display ) . '</strong>',
			'customer_name' => '<strong>' . esc_html( $name ) . '</strong>',
			'customer_mobile' => '<strong>' . esc_html( WPLWL_Mobile_Validator::national( $mobile ) ) . '</strong>',
			'site_title' => esc_html( get_bloginfo( 'name' ) ),
		);
		$notification = WPLWL_Mobile_Notifier::replace_tags( $notification_template, $tags );

		$sms_sent      = false;
		$sms_attempted = false;
		$sms_error     = '';
		$sms_status    = 'not-applicable';
		if ( 'win' === $prize['result'] ) {
			$sms_tags = array(
				'prize_label'     => $prize_display_label,
				'prize_value'     => $prize_value_display,
				'customer_name'   => $name,
				'customer_mobile' => WPLWL_Mobile_Validator::national( $mobile ),
				'site_title'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				'campaign'        => $campaign,
			);
			$template    = trim( (string) $this->mobile_settings->get( 'sms_win_message' ) );
			if ( '' === $template ) {
				$defaults = WPLWL_Mobile_Settings::defaults();
				$template = $defaults['sms_win_message'];
			}
			$sms_message = WPLWL_Mobile_Notifier::replace_tags( $template, $sms_tags );

			if ( 'on' !== $this->mobile_settings->get( 'sms_win_enable' ) ) {
				$sms_status = 'disabled';
				$sms_error  = 'ارسال پیامک جایزه در تنظیمات ماژول خاموش است.';
				$this->notifier->record_skipped( $mobile, $sms_message, 'prize-disabled', $sms_error );
			} else {
				$sms_attempted = true;
				$sms_status    = 'attempted';
				$sms_result    = $this->notifier->send( $mobile, $sms_message, array_merge( array( 'type' => 'prize' ), $sms_tags ) );
				$sms_sent      = ! is_wp_error( $sms_result );
				if ( is_wp_error( $sms_result ) ) {
					$sms_error  = $sms_result->get_error_message();
					$sms_status = 'failed';
				} else {
					$sms_status = 'sent';
				}
			}

			if ( $sms_sent ) {
				$notification .= '<br><small class="wplwl-mobile-sms-status wplwl-mobile-sms-success">مشخصات جایزه برای شماره شما پیامک شد.</small>';
			} elseif ( $sms_attempted ) {
				$notification .= '<br><small class="wplwl-mobile-sms-status wplwl-mobile-sms-error">جایزه شما ثبت شد، اما ارسال پیامک انجام نشد. لطفاً کد جایزه را همین‌جا نگه دارید.</small>';
			}
		}

		do_action( 'wplwl_mobile_after_spin', array(
			'mobile' => $mobile, 'name' => $name, 'campaign' => $campaign, 'prize' => $prize,
			'sms_attempted' => $sms_attempted, 'sms_sent' => $sms_sent, 'sms_status' => $sms_status, 'sms_error' => $sms_error,
		) );
		if ( 'win' === $prize['result'] ) {
			do_action( 'wplwl_mobile_prize_won', $mobile, $prize, $campaign, $name );
		}
		wp_send_json( array(
			'allow_spin' => 'yes', 'stop_position' => $prize['stop_position'], 'result' => $prize['result'],
			'result_notification' => do_shortcode( $notification ),
			'sms_attempted' => $sms_attempted, 'sms_sent' => $sms_sent, 'sms_status' => $sms_status, 'sms_error' => $sms_error, 'campaign' => $campaign,
		) );
	}
}
