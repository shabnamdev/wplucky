<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPLWL_Wheel_Engine' ) ) {
	class WPLWL_Wheel_Engine {
		const LOCK_OPTION = '_wplwl_mobile_prize_lock';

		public static function prepare_wheel( $wheel ) {
			$wheel = is_array( $wheel ) ? $wheel : array();
			$wheel['probability']    = isset( $wheel['probability'] ) && is_array( $wheel['probability'] ) ? array_values( $wheel['probability'] ) : array();
			$wheel['prize_type']     = isset( $wheel['prize_type'] ) && is_array( $wheel['prize_type'] ) ? array_values( $wheel['prize_type'] ) : array();
			$wheel['prize_quantity'] = isset( $wheel['prize_quantity'] ) && is_array( $wheel['prize_quantity'] ) ? array_values( $wheel['prize_quantity'] ) : array_fill( 0, count( $wheel['prize_type'] ), -1 );
			foreach ( $wheel['prize_type'] as $index => $type ) {
				if ( 'non' !== $type && isset( $wheel['prize_quantity'][ $index ] ) && 0 === (int) $wheel['prize_quantity'][ $index ] ) {
					$wheel['probability'][ $index ] = 0;
				}
			}
			return $wheel;
		}

		public static function get_result( $wheel ) {
			$wheel = self::prepare_wheel( $wheel );
			$weights = array_map( 'absint', $wheel['probability'] );
			$total   = array_sum( $weights );
			if ( $total < 1 ) {
				return 0;
			}
			$random = wp_rand( 1, $total );
			$sum    = 0;
			foreach ( $weights as $index => $weight ) {
				$sum += $weight;
				if ( $weight > 0 && $random <= $sum ) {
					return (int) $index;
				}
			}
			return 0;
		}

		public static function spin( $language = '' ) {
			for ( $attempt = 0; $attempt < 5; $attempt ++ ) {
				$params = get_option( '_wplwl_settings', array() );
				$data   = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance();
				$wheel  = self::prepare_wheel( isset( $params['wheel'] ) && is_array( $params['wheel'] ) ? $params['wheel'] : $data->get_params( 'wheel' ) );
				if ( empty( $wheel['probability'] ) || array_sum( array_map( 'absint', $wheel['probability'] ) ) < 1 ) {
					return new WP_Error( 'no_prize_probability', 'احتمال جایزه‌های چرخ به‌درستی تنظیم نشده است.' );
				}
				$stop = self::get_result( $wheel );
				$type = isset( $wheel['prize_type'][ $stop ] ) ? $wheel['prize_type'][ $stop ] : 'non';
				if ( 'non' === $type || self::reserve_quantity( $stop ) ) {
					$labels = $data->get_params( 'wheel', 'custom_label', $language );
					$values = isset( $wheel['custom_value'] ) && is_array( $wheel['custom_value'] ) ? $wheel['custom_value'] : array();
					$value  = isset( $values[ $stop ] ) ? $values[ $stop ] : '';
					$label  = isset( $labels[ $stop ] ) ? $labels[ $stop ] : '';
					$label  = str_replace( array( '{quantity_label}', '{prize_value}' ), array( '', $value ), $label );
					return array(
						'stop_position' => $stop,
						'result'        => 'non' === $type ? 'lost' : 'win',
						'prize_type'    => $type,
						'prize_label'   => $label,
						'prize_value'   => $value,
					);
				}
			}
			return new WP_Error( 'prize_reservation_failed', 'موجودی جایزه هم‌زمان تغییر کرده است. لطفاً دوباره گردونه را بچرخانید.' );
		}

		protected static function reserve_quantity( $stop ) {
			$settings = get_option( '_wplwl_settings', array() );
			$quantity = isset( $settings['wheel']['prize_quantity'][ $stop ] ) ? (int) $settings['wheel']['prize_quantity'][ $stop ] : -1;
			if ( $quantity < 0 ) {
				return true;
			}
			if ( 0 === $quantity ) {
				return false;
			}

			$token    = wp_generate_uuid4();
			$acquired = add_option( self::LOCK_OPTION, array( 'token' => $token, 'time' => microtime( true ) ), '', 'no' );
			if ( ! $acquired ) {
				$lock = get_option( self::LOCK_OPTION );
				if ( is_array( $lock ) && isset( $lock['time'] ) && microtime( true ) - (float) $lock['time'] > 5 ) {
					delete_option( self::LOCK_OPTION );
					$acquired = add_option( self::LOCK_OPTION, array( 'token' => $token, 'time' => microtime( true ) ), '', 'no' );
				}
			}
			if ( ! $acquired ) {
				return false;
			}

			$success = false;
			$current = get_option( '_wplwl_settings', array() );
			$current_quantity = isset( $current['wheel']['prize_quantity'][ $stop ] ) ? (int) $current['wheel']['prize_quantity'][ $stop ] : -1;
			if ( $current_quantity < 0 ) {
				$success = true;
			} elseif ( $current_quantity > 0 ) {
				$current['wheel']['prize_quantity'][ $stop ] = $current_quantity - 1;
				$success = update_option( '_wplwl_settings', $current );
				if ( ! $success && get_option( '_wplwl_settings' ) === $current ) {
					$success = true;
				}
			}
			$lock = get_option( self::LOCK_OPTION );
			if ( is_array( $lock ) && isset( $lock['token'] ) && $token === $lock['token'] ) {
				delete_option( self::LOCK_OPTION );
			}
			return $success;
		}
	}
}
