<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPLWL_Mobile_Validator' ) ) {
	class WPLWL_Mobile_Validator {
		public static function convert_digits( $value ) {
			return strtr( (string) $value, array(
				'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
				'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
				'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
				'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
			) );
		}

		public static function normalize( $mobile ) {
			$mobile = self::convert_digits( $mobile );
			$mobile = preg_replace( '/[^0-9+]/', '', $mobile );
			$mobile = trim( $mobile );

			if ( 0 === strpos( $mobile, '0098' ) ) {
				$mobile = '+98' . substr( $mobile, 4 );
			} elseif ( 0 === strpos( $mobile, '98' ) ) {
				$mobile = '+' . $mobile;
			} elseif ( 0 === strpos( $mobile, '09' ) ) {
				$mobile = '+98' . substr( $mobile, 1 );
			} elseif ( 0 === strpos( $mobile, '9' ) && 10 === strlen( $mobile ) ) {
				$mobile = '+98' . $mobile;
			}

			return $mobile;
		}

		public static function is_valid_iran_mobile( $mobile ) {
			return 1 === preg_match( '/^\+989\d{9}$/', self::normalize( $mobile ) );
		}

		public static function national( $mobile ) {
			$mobile = self::normalize( $mobile );
			return self::is_valid_iran_mobile( $mobile ) ? '0' . substr( $mobile, 3 ) : '';
		}

		public static function mask( $mobile ) {
			$national = self::national( $mobile );
			if ( ! $national ) {
				return $mobile;
			}
			return substr( $national, 0, 4 ) . '***' . substr( $national, -4 );
		}
	}
}
