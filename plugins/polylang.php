<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WORDPRESS_LUCKY_WHEEL_Plugins_Polylang extends WPLWL_Multi_Languages {
	public function __construct() {
		if ( ! class_exists('Polylang') ) {
			return;
		}
		parent::__construct();
	}
	public function get_current_language() {
		if ( isset( $this->cache['current_language'] ) ) {
			return $this->cache['current_language'];
		}
		$current_language = pll_current_language( 'slug' );
		if ($current_language === $this->get_default_language()){
			$current_language ='';
		}
		$this->cache['current_language'] = $current_language;
		return $this->cache['current_language'];
	}
	public function get_default_language() {
		if ( isset( $this->cache['default_language']) ) {
			return $this->cache['default_language'];
		}
		$this->cache['default_language'] = pll_default_language( 'slug' );
		return $this->cache['default_language'];
	}
	public function get_languages_data() {
		if ( isset( $this->cache['languages_data'] ) ) {
			return $this->cache['languages_data'];
		}
		$languages_data =[];
		$languages = pll_languages_list(array( 'fields' => '' ) );
		foreach ($languages as $language){
			$language = new PLL_Language($language);
			$languages_data[$language->slug]=[
				'code' => $language->slug,
				'translated_name' => $language->name,
				'url' => $language->search_url ,
				'country_flag_url' => $language->custom_flag_url ?: $language->flag_url,
			];
		}
		$this->cache['languages_data'] = $languages_data;
		return $this->cache['languages_data'];
	}
}