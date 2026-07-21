<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPLWL_Mobile_Notifier' ) ) {
	class WPLWL_Mobile_Notifier {
		const DEPENDENCY_SLUG = 'persian-woocommerce-sms';
		const LOG_OPTION      = '_wplwl_mobile_sms_logs';
		const LOG_LIMIT       = 50;

		protected $settings;

		public function __construct() {
			$this->settings = WPLWL_Mobile_Settings::get_instance();
		}

		




		public static function get_service() {
			if ( ! function_exists( 'PWooSMS' ) ) {
				return new WP_Error( 'pwoosms_function_missing', 'تابع عمومی افزونه «پیامک ووکامرس فارسی» در دسترس نیست.' );
			}

			try {
				$service = PWooSMS();
			} catch ( Throwable $exception ) {
				return new WP_Error( 'pwoosms_boot_error', 'راه‌اندازی افزونه پیامک با خطا روبه‌رو شد: ' . sanitize_text_field( $exception->getMessage() ) );
			}

			if ( ! is_object( $service ) ) {
				return new WP_Error( 'pwoosms_invalid_service', 'افزونه پیامک، شیء سرویس معتبری برنگرداند.' );
			}

			if ( ! is_callable( array( $service, 'SendSMS' ) ) ) {
				return new WP_Error( 'pwoosms_method_missing', 'متد عمومی SendSMS در نسخه نصب‌شده افزونه پیامک در دسترس نیست.' );
			}

			return $service;
		}

		




		public static function is_pwoosms_available() {
			return ! is_wp_error( self::get_service() );
		}

		




		public static function dependency_version() {
			if ( ! function_exists( 'get_file_data' ) ) {
				return '';
			}

			$files = array(
				WP_PLUGIN_DIR . '/persian-woocommerce-sms/WoocommerceIR_SMS.php',
				WP_PLUGIN_DIR . '/persian-woocommerce-sms/persian-woocommerce-sms.php',
				WP_PLUGIN_DIR . '/persian-woocommerce-sms/woocommerce-sms.php',
			);

			foreach ( $files as $file ) {
				if ( is_readable( $file ) ) {
					$data = get_file_data( $file, array( 'Version' => 'Version' ), 'plugin' );
					if ( ! empty( $data['Version'] ) ) {
						return sanitize_text_field( $data['Version'] );
					}
				}
			}

			return '';
		}

		




		public static function dependency_status() {
			$service = self::get_service();
			$version = self::dependency_version();

			if ( ! is_wp_error( $service ) ) {
				return array(
					'available'     => true,
					'version'       => $version,
					'service_class' => get_class( $service ),
					'message'       => 'افزونه «پیامک ووکامرس فارسی» فعال است و رابط عمومی ارسال پیامک در دسترس قرار دارد' . ( $version ? '؛ نسخه شناسایی‌شده: ' . $version : '' ) . '.',
				);
			}

			return array(
				'available'     => false,
				'version'       => $version,
				'service_class' => '',
				'message'       => $service->get_error_message(),
			);
		}

		





		public static function interpret_response( $response ) {
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( true === $response ) {
				return array( 'success' => true, 'response' => $response );
			}

			if ( false === $response || null === $response ) {
				return new WP_Error( 'pwoosms_empty_response', 'درگاه پیامکی هیچ پاسخ موفقی برنگرداند.' );
			}

			if ( is_int( $response ) || is_float( $response ) ) {
				if ( (float) $response > 0 ) {
					return array( 'success' => true, 'response' => $response );
				}
				return new WP_Error( 'pwoosms_numeric_failure', 'درگاه پیامکی کد ناموفق «' . sanitize_text_field( (string) $response ) . '» را برگرداند.' );
			}

			if ( is_string( $response ) ) {
				$text       = trim( wp_strip_all_tags( $response ) );
				$normalized = strtolower( $text );

				if ( '' === $text ) {
					return new WP_Error( 'pwoosms_empty_string', 'درگاه پیامکی پاسخ خالی برگرداند.' );
				}
				if ( is_numeric( $text ) && (float) $text > 0 ) {
					return array( 'success' => true, 'response' => $response );
				}

				$error_words = array( 'error', 'failed', 'failure', 'invalid', 'unauthorized', 'forbidden', 'خطا', 'ناموفق', 'نامعتبر', 'غیرمجاز', 'عدم ارسال', 'ارسال نشد' );
				foreach ( $error_words as $word ) {
					if ( false !== strpos( $normalized, $word ) ) {
						return new WP_Error( 'pwoosms_text_failure', 'پاسخ درگاه: ' . sanitize_text_field( $text ) );
					}
				}

				$success_words = array( 'success', 'successful', 'sent', 'ok', 'موفق', 'ارسال شد', 'ثبت شد' );
				foreach ( $success_words as $word ) {
					if ( false !== strpos( $normalized, $word ) ) {
						return array( 'success' => true, 'response' => $response );
					}
				}

				return new WP_Error( 'pwoosms_ambiguous_response', 'درگاه پاسخ نامشخصی برگرداند: ' . sanitize_text_field( $text ) );
			}

			if ( is_object( $response ) ) {
				$response = get_object_vars( $response );
			}

			if ( is_array( $response ) ) {
				if ( isset( $response['success'] ) ) {
					if ( true === $response['success'] || 1 === $response['success'] || '1' === $response['success'] || 'true' === strtolower( (string) $response['success'] ) ) {
						return array( 'success' => true, 'response' => $response );
					}
					return new WP_Error( 'pwoosms_array_failure', self::array_error_message( $response ) );
				}

				foreach ( array( 'error', 'errors', 'exception' ) as $error_key ) {
					if ( ! empty( $response[ $error_key ] ) ) {
						return new WP_Error( 'pwoosms_array_error', self::array_error_message( $response ) );
					}
				}

				if ( isset( $response['status'] ) ) {
					$status = strtolower( trim( (string) $response['status'] ) );
					if ( in_array( $status, array( '1', 'true', 'success', 'successful', 'sent', 'ok', '200', '201' ), true ) ) {
						return array( 'success' => true, 'response' => $response );
					}
					if ( in_array( $status, array( '0', 'false', 'error', 'failed', 'failure', 'invalid', '400', '401', '403', '500' ), true ) ) {
						return new WP_Error( 'pwoosms_status_failure', self::array_error_message( $response ) );
					}
				}

				if ( isset( $response['code'] ) && in_array( (string) $response['code'], array( '200', '201' ), true ) ) {
					return array( 'success' => true, 'response' => $response );
				}

				foreach ( array( 'message_id', 'messageId', 'id', 'recId', 'rec_id' ) as $id_key ) {
					if ( ! empty( $response[ $id_key ] ) ) {
						return array( 'success' => true, 'response' => $response );
					}
				}

				return new WP_Error( 'pwoosms_unknown_array', 'پاسخ درگاه قابل تشخیص نبود: ' . self::response_summary( $response ) );
			}

			return new WP_Error( 'pwoosms_unknown_response', 'نوع پاسخ درگاه پیامکی قابل تشخیص نبود.' );
		}

		protected static function array_error_message( $response ) {
			foreach ( array( 'message', 'error', 'errors', 'description', 'detail' ) as $key ) {
				if ( isset( $response[ $key ] ) && '' !== trim( (string) $response[ $key ] ) ) {
					return 'پاسخ درگاه: ' . sanitize_text_field( is_array( $response[ $key ] ) ? wp_json_encode( $response[ $key ], JSON_UNESCAPED_UNICODE ) : (string) $response[ $key ] );
				}
			}
			return 'درگاه پیامکی، ارسال را ناموفق اعلام کرد.';
		}

		







		public function send( $mobile, $message, $context = array() ) {
			$mobile_e164 = WPLWL_Mobile_Validator::normalize( $mobile );
			$mobile      = WPLWL_Mobile_Validator::national( $mobile_e164 );
			$message     = trim( wp_strip_all_tags( (string) $message ) );
			$context     = is_array( $context ) ? $context : array();
			$type        = ! empty( $context['type'] ) ? sanitize_key( $context['type'] ) : 'general';

			$pre = apply_filters( 'wplwl_mobile_sms_send', null, $mobile, $message, $context, $this->settings->get() );
			if ( null !== $pre ) {
				$result = self::interpret_response( $pre );
				$this->record_result( $result, $mobile_e164, $message, $type, $pre, 'custom-filter' );
				return is_wp_error( $result ) ? $result : array_merge( $result, array( 'provider' => 'custom-filter' ) );
			}

			$service = self::get_service();
			if ( is_wp_error( $service ) ) {
				$this->record_result( $service, $mobile_e164, $message, $type, null, self::DEPENDENCY_SLUG );
				return $service;
			}

			if ( ! WPLWL_Mobile_Validator::is_valid_iran_mobile( $mobile_e164 ) || ! $mobile || ! $message ) {
				$error = new WP_Error( 'pwoosms_invalid_payload', 'شماره موبایل یا متن پیامک معتبر نیست.' );
				$this->record_result( $error, $mobile_e164, $message, $type, null, self::DEPENDENCY_SLUG );
				return $error;
			}

			$data = array(
				'type'    => 1,
				'mobile'  => $mobile,
				'message' => $message,
			);
			$data = apply_filters( 'wplwl_mobile_pwoosms_payload', $data, $context, $this->settings->get() );

			try {
				$response = call_user_func( array( $service, 'SendSMS' ), $data );
			} catch ( Throwable $exception ) {
				$error = new WP_Error( 'pwoosms_exception', 'ارسال پیامک با خطا روبه‌رو شد: ' . sanitize_text_field( $exception->getMessage() ) );
				$this->record_result( $error, $mobile_e164, $message, $type, null, self::DEPENDENCY_SLUG );
				return $error;
			}

			$result = self::interpret_response( $response );
			$this->record_result( $result, $mobile_e164, $message, $type, $response, self::DEPENDENCY_SLUG );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$result['provider'] = self::DEPENDENCY_SLUG;
			return $result;
		}

		









		public function record_skipped( $mobile, $message, $type, $detail ) {
			$mobile_e164 = WPLWL_Mobile_Validator::normalize( $mobile );
			$error       = new WP_Error( 'wplwl_sms_skipped', sanitize_text_field( $detail ) );
			$this->record_result( $error, $mobile_e164, (string) $message, sanitize_key( $type ), null, 'module-setting' );
		}

		protected function record_result( $result, $mobile, $message, $type, $raw_response, $provider ) {
			$success = ! is_wp_error( $result );
			$entry   = array(
				'id'       => wp_generate_uuid4(),
				'time'     => time(),
				'type'     => sanitize_key( $type ),
				'mobile'   => WPLWL_Mobile_Validator::mask( $mobile ),
				'success'  => $success ? 1 : 0,
				'message'  => function_exists( 'mb_substr' ) ? mb_substr( $message, 0, 250 ) : substr( $message, 0, 250 ),
				'provider' => sanitize_key( $provider ),
				'detail'   => $success ? self::response_summary( $raw_response ) : $result->get_error_message(),
			);

			$logs = self::get_logs();
			array_unshift( $logs, $entry );
			$logs = array_slice( $logs, 0, self::LOG_LIMIT );
			update_option( self::LOG_OPTION, $logs, false );

			do_action( 'wplwl_mobile_sms_result', $entry, $raw_response, $result );
		}

		public static function get_logs() {
			$logs = get_option( self::LOG_OPTION, array() );
			return is_array( $logs ) ? $logs : array();
		}

		public static function clear_logs() {
			delete_option( self::LOG_OPTION );
		}

		public static function response_summary( $response ) {
			if ( null === $response ) {
				return 'پاسخ خالی';
			}
			if ( true === $response ) {
				return 'true';
			}
			if ( false === $response ) {
				return 'false';
			}
			if ( is_scalar( $response ) ) {
				$text = sanitize_text_field( (string) $response );
				return function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 300 ) : substr( $text, 0, 300 );
			}
			if ( is_object( $response ) ) {
				$response = get_object_vars( $response );
			}
			$text = wp_json_encode( self::sanitize_log_value( $response ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			return function_exists( 'mb_substr' ) ? mb_substr( (string) $text, 0, 500 ) : substr( (string) $text, 0, 500 );
		}

		protected static function sanitize_log_value( $value, $depth = 0 ) {
			if ( $depth > 3 ) {
				return '[بیش از حد تو‌در‌تو]';
			}
			if ( is_object( $value ) ) {
				$value = get_object_vars( $value );
			}
			if ( is_array( $value ) ) {
				$output = array();
				foreach ( $value as $key => $item ) {
					$key_text = strtolower( (string) $key );
					if ( preg_match( '/password|passwd|token|secret|apikey|api_key|accesskey|access_key|credential|authorization/', $key_text ) ) {
						$output[ $key ] = '[پنهان شد]';
						continue;
					}
					$output[ $key ] = self::sanitize_log_value( $item, $depth + 1 );
				}
				return $output;
			}
			if ( is_bool( $value ) || is_numeric( $value ) || null === $value ) {
				return $value;
			}
			return sanitize_text_field( (string) $value );
		}

		public static function replace_tags( $template, $values ) {
			$search  = array();
			$replace = array();
			foreach ( $values as $key => $value ) {
				$search[]  = '{' . $key . '}';
				$replace[] = $value;
			}
			return str_replace( $search, $replace, $template );
		}
	}
}
