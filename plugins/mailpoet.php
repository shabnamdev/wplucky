<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WORDPRESS_LUCKY_WHEEL_Plugins_Mailpoet{
	protected $settings;
	public function __construct() {
		if (!class_exists( \MailPoet\API\API::class )){
			return;
		}
		add_action('wplwl_settings_tab',[$this,'wplwl_settings_tab'],17,1);
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
				'enable_mailpoet' => [
					'type'  => 'checkbox',
					'html' => sprintf('<div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="enable_mailpoet"
                                               id="enable_mailpoet" %s value="1">
                                        <label for="enable_mailpoet"></label>
                                    </div>',checked( $this->settings->get_params( 'enable_mailpoet' ), 1 )),
					'desc'  =>  esc_html__( 'Turn on to use MailPoet system', 'wordpress-lucky-wheel' ) ,
					'title' => esc_html__( 'MailPoet', 'wordpress-lucky-wheel' ),
				],
				'mailpoet_list'       => [
					'wrap_class'     => 'wplwl-enable_mailpoet-class',
					'type'     => 'select',
					'title'    => esc_html__( 'MailPoet lists', 'wordpress-lucky-wheel' ),
				],
			],
		];
		$mailpoet_api   = \MailPoet\API\API::MP( 'v1' );
		$mailpoet_lists = $mailpoet_api->getLists();
		$mailpoet_selected_list = $this->settings->get_params( 'mailpoet_list' );
		ob_start();
		?>

		<select class="vi-ui fluid dropdown" name="mailpoet_list[]"
		        id="mailpoet_list" multiple>
			<?php
			foreach ( $mailpoet_lists as $list ) {
                if (empty($list['id'])){
                    continue;
                }
				$selected = in_array( $list['id'], (array) $mailpoet_selected_list ) ? 'selected' : '';
				printf( '<option value="%s" %s>%s</option>',
					esc_attr( $list['id'] ), esc_attr( $selected ), esc_html( $list['name'] ?? $list['id'] ) );
			}
			?>
		</select>
		<?php
		$fields['fields']['mailpoet_list']['html'] = ob_get_clean();
		$this->settings::villatheme_render_table_field( $fields );
	}

}