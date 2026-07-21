<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WORDPRESS_LUCKY_WHEEL_Plugins_Funnelkit{
	protected $settings;
	public function __construct() {
		if (!class_exists( 'BWFCRM_Contact' ) ){
			return;
		}
		add_action('wplwl_settings_tab',[$this,'wplwl_settings_tab'],20,1);
	}
	public function wplwl_settings_tab($tab){
		if ($tab !== 'email_api'){
			return;
		}
		$this->settings = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance(true);
		$fields = [
			'section_start' => [],
			'section_end'   => [],
			'fields'        => [
				'enable_funnelkit' => [
					'type'  => 'checkbox',
					'html' => sprintf('<div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="enable_funnelkit"
                                               id="enable_funnelkit" %s value="1">
                                        <label for="enable_funnelkit"></label>
                                    </div>',checked( $this->settings->get_params( 'enable_funnelkit' ), 1 )),
					'desc'  =>  esc_html__( 'Turn on to use FunnelKit system', 'wordpress-lucky-wheel' ) ,
					'title' => esc_html__( 'FunnelKit', 'wordpress-lucky-wheel' ),
				],
				'funnelkit_list'       => [
					'wrap_class'     => 'wplwl-enable_funnelkit-class',
					'type'     => 'select',
					'title'    => esc_html__( 'FunnelKit lists', 'wordpress-lucky-wheel' ),
				],
				'funnelkit_status'       => [
					'wrap_class'     => 'wplwl-enable_funnelkit-class',
					'type'     => 'select',
					'title'    => esc_html__( 'FunnelKit Status', 'wordpress-lucky-wheel' ),
				],
			],
		];
		$funnelkit_lists = BWFCRM_Lists::get_lists();
		$funnelkit_selected_list = $this->settings->get_params( 'funnelkit_list' );
		ob_start();
		?>
        <select class="vi-ui fluid dropdown" name="funnelkit_list[]"
                id="funnelkit_list" multiple>
			<?php
			foreach ( $funnelkit_lists as $list ) {
                if (empty($list['ID'])){
                    continue;
                }
				$selected = in_array( $list['ID'], (array) $funnelkit_selected_list ) ? 'selected' : '';
				printf( '<option value="%s" %s>%s</option>',
					esc_attr( $list['ID'] ), esc_attr( $selected ), esc_html( $list['name'] ?? $list['ID']) );
			}
			?>
        </select>
		<?php
		$fields['fields']['funnelkit_list']['html'] = ob_get_clean();
        $funnelkit_status= $this->settings->get_params( 'funnelkit_status' );
		ob_start();
		?>
        <select class="vi-ui fluid dropdown" name="funnelkit_status" id="funnelkit_status">
            <option value="0" <?php selected( $funnelkit_status, '0' ) ?>><?php esc_html_e( 'Unverified', 'wordpress-lucky-wheel' ) ?></option>
            <option value="1" <?php selected( $funnelkit_status, '1' ) ?>><?php esc_html_e( 'Subscribed', 'wordpress-lucky-wheel' ) ?></option>
            <option value="2" <?php selected( $funnelkit_status, '2' ) ?>><?php esc_html_e( 'Bounced', 'wordpress-lucky-wheel' ) ?></option>
        </select>
		<?php
		$fields['fields']['funnelkit_status']['html'] = ob_get_clean();
		$this->settings::villatheme_render_table_field( $fields );
	}

}