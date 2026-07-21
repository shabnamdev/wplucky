<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WORDPRESS_LUCKY_WHEEL_Plugins_Mailster{
	protected $settings;
	public function __construct() {
		if (!function_exists( 'mailster' )){
			return;
		}
		add_action('wplwl_settings_tab',[$this,'wplwl_settings_tab'],18,1);
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
				'enable_mailster' => [
					'type'  => 'checkbox',
					'html' => sprintf('<div class="vi-ui toggle checkbox checked">
                                        <input type="checkbox" name="enable_mailster"
                                               id="enable_mailster" %s value="1">
                                        <label for="enable_mailster"></label>
                                    </div>',checked( $this->settings->get_params( 'enable_mailster' ), 1 )),
					'desc'  =>  esc_html__( 'Turn on to use Mailster system', 'wordpress-lucky-wheel' ) ,
					'title' => esc_html__( 'Mailster', 'wordpress-lucky-wheel' ),
				],
				'mailster_list'       => [
					'wrap_class'     => 'wplwl-enable_mailster-class',
					'type'     => 'select',
					'title'    => esc_html__( 'Mailster lists', 'wordpress-lucky-wheel' ),
				],
			],
		];
		$mailster_lists = mailster( 'lists' )->get();
		$mailster_selected_list = $this->settings->get_params( 'mailster_list' ) ?? [];
		ob_start();
		?>
        <select class="vi-ui fluid dropdown" name="mailster_list[]"
                id="mailster_list" multiple>
			<?php
			foreach ( $mailster_lists as $list ) {
				$selected = in_array( $list->ID, (array) $mailster_selected_list ) ? 'selected' : '';
				printf( '<option value="%s" %s>%s</option>',
					esc_attr( $list->ID ), esc_attr( $selected ), esc_html( $list->name ) );
			}
			?>
        </select>
		<?php
		$fields['fields']['mailster_list']['html'] = ob_get_clean();
		$this->settings::villatheme_render_table_field( $fields );
	}

}