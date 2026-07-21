<?php
/**
 * Function include all files in folder
 *
 * @param $path   Directory address
 * @param $ext    array file extension what will include
 * @param $prefix string Class prefix
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'vi_include_folder' ) ) {
	function vi_include_folder( $path, $prefix = '', $ext = array( 'php' ) ) {

		/*Include all files in payment folder*/
		if ( ! is_array( $ext ) ) {
			$ext = explode( ',', $ext );
			$ext = array_map( 'trim', $ext );
		}
		$sfiles = scandir( $path );
		foreach ( $sfiles as $sfile ) {
			if ( $sfile != '.' && $sfile != '..' ) {
				if ( is_file( $path . "/" . $sfile ) ) {
					$ext_file  = pathinfo( $path . "/" . $sfile );
					$file_name = $ext_file['filename']??'';
					if ($file_name && !empty( $ext_file['extension']) ) {
						if ( in_array( $ext_file['extension'], $ext ) ) {
							$class = preg_replace( '/\W/i', '_', $prefix . ucfirst( $file_name ) );

							if ( ! class_exists( $class ) ) {
								require_once $path . $sfile;
								if ( class_exists( $class ) ) {
									new $class;
								}
							}
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'wplwl_get_explode' ) ) {
	function wplwl_get_explode( $string, $sap = ',', $limit = 3 ) {
		$rand       = 0;
		$show_wheel = explode( $sap, $string, $limit );
		$show_wheel = array_map( 'absInt', $show_wheel );
		if ( sizeof( $show_wheel ) > 1 ) {
			$rand = $show_wheel[0] < $show_wheel[1] ? wp_rand( $show_wheel[0], $show_wheel[1] ) : wp_rand( $show_wheel[1], $show_wheel[0] );
		} else {
			$rand = $show_wheel[0];
		}

		return $rand;
	}
}

if ( ! function_exists( 'wplwl_sanitize_text_field' ) ) {
	function wplwl_sanitize_text_field( $string ) {
		return sanitize_text_field( stripslashes( $string ) );
	}
}
if ( ! function_exists( 'villatheme_sanitize_fields' ) ) {
	function villatheme_sanitize_fields( $data ) {
		if ( is_array( $data ) ) {
			return array_map( 'villatheme_sanitize_fields', $data );
		} else {
			return is_scalar( $data ) ? sanitize_text_field( wp_unslash( $data ) ) : $data;
		}
	}
}
if ( ! function_exists( 'villatheme_json_encode' ) ) {
	function villatheme_json_encode( $value, $options = 256, $depth = 512 ) {
		return wp_json_encode( $value, $options, $depth );
	}
}
if ( ! function_exists( 'villatheme_json_decode' ) ) {
	function villatheme_json_decode( $json, $assoc = true, $depth = 512, $options = 2 ) {
		if ( is_array( $json ) ) {
			return $json;
		}
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$json = mb_convert_encoding( $json, 'UTF-8', 'UTF-8' );
		}

		return json_decode( is_string( $json ) ? $json : '{}', $assoc, $depth, $options );
	}
}