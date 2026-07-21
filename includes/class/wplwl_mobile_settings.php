<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPLWL_Mobile_Settings' ) ) {
	class WPLWL_Mobile_Settings {
		const OPTION_KEY     = '_wplwl_mobile_settings';
		const VERSION_KEY    = '_wplwl_mobile_module_version';
		const MODULE_VERSION = '1.5.0';

		protected static $instance = null;
		protected $params = array();

		public static function get_instance( $refresh = false ) {
			if ( $refresh || null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			$saved = get_option( self::OPTION_KEY, array() );
			$saved = is_array( $saved ) ? $saved : array();

			






			$installed_version = (string) get_option( self::VERSION_KEY, '' );
			if ( '' === $installed_version || version_compare( $installed_version, '1.4.4', '<' ) ) {
				$saved['sms_win_enable'] = 'on';
				update_option( self::OPTION_KEY, $saved, false );
			}
			if ( version_compare( $installed_version, self::MODULE_VERSION, '<' ) ) {
				update_option( self::VERSION_KEY, self::MODULE_VERSION, false );
			}

			 
			$legacy_texts = array(
				'otp_message'              => 'کد تأیید شما: {otp} - {site_title}',
				'sms_win_message'          => 'تبریک! شما برنده {prize_label} شدید. کد جایزه: {prize_value}',
				'win_notification'         => 'تبریک! شما برنده {prize_label} شدید. کد جایزه: {prize_value}',
				'lost_notification'        => 'این بار برنده نشدید؛ دوباره شانس خود را امتحان کنید.',
				'marketing_consent_text'   => 'مایلم پیامک‌های تخفیف و پیشنهادهای ویژه را دریافت کنم.',
			);
			foreach ( $legacy_texts as $key => $legacy_value ) {
				if ( isset( $saved[ $key ] ) && $legacy_value === $saved[ $key ] ) {
					unset( $saved[ $key ] );
				}
			}

			$this->params = wp_parse_args( $saved, self::defaults() );
		}

		public static function defaults() {
			return array(
				'enable'                   => 'off',
				'name_enable'              => 'on',
				'name_required'            => 'off',
				'prefill_billing_phone'    => 'on',
				'suppress_email_popup'     => 'on',
				'spin_num'                 => 0,
				'delay'                    => 0,
				'delay_unit'               => 'inherit',
				'otp_enable'               => 'off',
				'otp_length'               => 5,
				'otp_expiry'               => 3,
				'otp_resend_delay'         => 60,
				'otp_message'              => "رمز یک‌بارمصرف شما در {site_title}: {otp}\nاین رمز تا چند دقیقه معتبر است.",
				'sms_win_enable'           => 'on',
				'sms_win_message'          => "تبریک! شما در چرخ شانس {site_title} برنده «{prize_label}» شدید.\nکد یا مقدار جایزه: {prize_value}",
				'win_notification'         => 'تبریک! جایزه شما «{prize_label}» است.<br>کد یا مقدار جایزه: {prize_value}',
				'lost_notification'        => 'این بار جایزه‌ای نصیبتان نشد؛ امیدواریم در نوبت بعدی برنده شوید.',
				'marketing_consent_enable' => 'off',
				'marketing_consent_text'   => 'مایلم پیامک‌های مربوط به تخفیف‌ها و پیشنهادهای ویژه را دریافت کنم.',
				'mask_mobile_admin'        => 'on',
				'rate_limit_seconds'       => 3,
				'honeypot_enable'          => 'on',
			);
		}

		public function get( $key = null, $default = null ) {
			if ( null === $key ) {
				return $this->params;
			}
			return array_key_exists( $key, $this->params ) ? $this->params[ $key ] : $default;
		}

		public static function sanitize( $input ) {
			$defaults = self::defaults();
			$input    = is_array( $input ) ? $input : array();
			$output   = $defaults;

			$checkboxes = array(
				'enable',
				'name_enable',
				'name_required',
				'prefill_billing_phone',
				'suppress_email_popup',
				'otp_enable',
				'sms_win_enable',
				'marketing_consent_enable',
				'mask_mobile_admin',
				'honeypot_enable',
			);
			foreach ( $checkboxes as $key ) {
				$output[ $key ] = ! empty( $input[ $key ] ) && 'on' === $input[ $key ] ? 'on' : 'off';
			}

			$output['spin_num']               = absint( isset( $input['spin_num'] ) ? $input['spin_num'] : 0 );
			$output['delay']                  = absint( isset( $input['delay'] ) ? $input['delay'] : 0 );
			$output['delay_unit']             = isset( $input['delay_unit'] ) && in_array( $input['delay_unit'], array( 'inherit', 's', 'm', 'h', 'd' ), true ) ? $input['delay_unit'] : 'inherit';
			$output['otp_length']             = min( 8, max( 4, absint( isset( $input['otp_length'] ) ? $input['otp_length'] : 5 ) ) );
			$output['otp_expiry']             = min( 15, max( 1, absint( isset( $input['otp_expiry'] ) ? $input['otp_expiry'] : 3 ) ) );
			$output['otp_resend_delay']       = min( 600, max( 30, absint( isset( $input['otp_resend_delay'] ) ? $input['otp_resend_delay'] : 60 ) ) );
			$output['otp_message']            = sanitize_textarea_field( isset( $input['otp_message'] ) ? wp_unslash( $input['otp_message'] ) : $defaults['otp_message'] );
			$output['sms_win_message']        = sanitize_textarea_field( isset( $input['sms_win_message'] ) ? wp_unslash( $input['sms_win_message'] ) : $defaults['sms_win_message'] );
			$output['win_notification']       = wp_kses_post( isset( $input['win_notification'] ) ? wp_unslash( $input['win_notification'] ) : $defaults['win_notification'] );
			$output['lost_notification']      = wp_kses_post( isset( $input['lost_notification'] ) ? wp_unslash( $input['lost_notification'] ) : $defaults['lost_notification'] );
			$output['marketing_consent_text'] = wp_kses_post( isset( $input['marketing_consent_text'] ) ? wp_unslash( $input['marketing_consent_text'] ) : $defaults['marketing_consent_text'] );
			$output['rate_limit_seconds']     = min( 60, max( 1, absint( isset( $input['rate_limit_seconds'] ) ? $input['rate_limit_seconds'] : 3 ) ) );

			return $output;
		}
	}
}
