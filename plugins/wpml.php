<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WORDPRESS_LUCKY_WHEEL_Plugins_Wpml extends WPLWL_Multi_Languages {
	public function __construct() {
		if ( ! is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			return;
		}
        parent::__construct();
	}
	public function get_current_language() {
		if ( isset( $this->cache['current_language'] ) ) {
			return $this->cache['current_language'];
		}
		global $sitepress;
		$current_language = $sitepress->get_current_language();
        if ($current_language == $this->get_default_language()){
            $current_language ='';
        }
		$this->cache['current_language'] = $current_language;
		return $this->cache['current_language'];
	}
	public function get_default_language() {
		if ( isset( $this->cache['default_language']) ) {
			return $this->cache['default_language'];
		}
		global $sitepress;
		$this->cache['default_language'] = $sitepress->get_default_language();
		return $this->cache['default_language'];
	}
	public function get_languages_data() {
		if ( isset( $this->cache['languages_data'] ) ) {
			return $this->cache['languages_data'];
		}
		$this->cache['languages_data'] = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
		return $this->cache['languages_data'];
	}
}