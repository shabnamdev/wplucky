<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
	return;
}
add_action( 'elementor/widgets/widgets_registered', function () {
	if ( is_file( VI_WORDPRESS_LUCKY_WHEEL_INCLUDES . 'elementor/widget.php' ) ) {
		require_once( 'widget.php' );
		$widget = new WPLWL_Elementor_Wheel_Widget();
		Elementor\Plugin::instance()->widgets_manager->register_widget_type( $widget );
	}
} );