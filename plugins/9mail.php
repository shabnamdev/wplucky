<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WORDPRESS_LUCKY_WHEEL_Plugins_9mail {
	public static $email_templates, $is_active;
	public $settings, $wheel_email_templates;

	public function __construct() {
		if ( ! class_exists( '\EmTmpl\EMTMPL_Email_Templates_Designer' )) {
			return;
		}
		self::$is_active = true;
		add_action( 'wplwl_wheel_settings_slices_column', [ $this, 'wplwl_wheel_settings_slices_column' ] );
		add_action( 'wplwl_wheel_settings_slices_column_content', [ $this, 'wplwl_wheel_settings_slices_column_content' ], 10, 1 );

		add_filter( 'emtmpl_user_emails', array( $this, 'register_email_type' ) );
		add_filter( 'emtmpl_sample_subjects', array( $this, 'register_email_sample_subject' ) );
		add_filter( 'emtmpl_sample_templates', array( $this, 'register_email_sample_template' ) );
		add_filter( 'emtmpl_shortcode_for_editor', array( $this, 'register_render_preview_shortcode' ) );
	}

	public function wplwl_wheel_settings_slices_column_content( $index ) {
		if ( ! $this->settings ) {
			$this->settings = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance( true );
		}
		$all_email_templates  = self::get_email_templates();
		$wheel_email_template = $this->wheel_email_templates[ $index ] ?? '';
		echo ' <td class="wheel_email_template">';
		ob_start();
		?>
        <select class="vi-ui dropdown fluid" name="email_templates[]">
            <option value="0"><?php esc_html_e( 'None', 'wordpress-lucky-wheel' ) ?></option>
			<?php
			if ( ! empty( $all_email_templates ) ) {
				foreach ( $all_email_templates as $all_email_templates_v ) {
					?>
                    <option value="<?php echo esc_attr( $all_email_templates_v->ID ); ?>"<?php selected( $all_email_templates_v->ID, $wheel_email_template ); ?>>
						<?php echo esc_html( "(#{$all_email_templates_v->ID}){$all_email_templates_v->post_title}" ); ?>
                    </option>
					<?php
				}
			}
			?>
        </select>
		<?php
		$wheel_email_template_html = ob_get_clean();
		$fields                    = [
			'fields' => [
				'email_templates' => [
					'not_wrap_html'         => 1,
					'wheel_slide_index'     => $index,
					'wheel_email_templates' => $all_email_templates,
					'html'                  => $wheel_email_template_html,
				]
			],
		];
		$this->settings::villatheme_render_table_field( $fields );
		echo '</td>';
	}

	public function wplwl_wheel_settings_slices_column() {
		$this->settings              = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance( true );
		$this->wheel_email_templates = $this->settings->get_params( 'wheel', 'email_templates' );
		if ( ! is_array( $this->wheel_email_templates ) ) {
			$this->wheel_email_templates = [];
		}
		?>
        <th rowspan="2"><?php esc_html_e( 'Email Template', 'wordpress-lucky-wheel' ) ?></th>
		<?php
	}

	public static function get_email_templates( $type = 'wplwl_coupon_email' ) {
		if ( ! self::$email_templates ) {
			self::$email_templates = emtmpl_get_emails_list( $type );
		}

		return self::$email_templates;
	}

	public function register_email_type( $emails ) {
		$emails['wplwl_coupon_email'] = esc_html__( 'Wordpress Lucky Wheel - Coupon Email', 'wordpress-lucky-wheel' );

		return $emails;
	}

	public function register_email_sample_subject( $subjects ) {
		$subjects['wplwl_coupon_email'] = 'Congratulation from {wplwl_site_title}';

		return $subjects;
	}

	public function register_email_sample_template( $samples ) {
        $data ='{"style_container":{"background-color":"transparent","background-image":"none","width":600,"responsive":"380"},"rows":{"0":{"props":{"style_outer":{"padding":"15px 35px","background-image":"none","background-color":"#162447","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"transparent","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"530px"}},"elements":{"0":{"type":"html/text","style":{"width":"530px","line-height":"30px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlciI+PHNwYW4gc3R5bGU9ImNvbG9yOiAjZmZmZmZmIj57d3Bsd2xfc2l0ZV90aXRsZX08L3NwYW4+PC9wPg=="},"attrs":{"data-center_on_mobile":""},"childStyle":{}}}}}},"1":{"props":{"style_outer":{"padding":"25px","background-image":"none","background-color":"#f9f9f9","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"550px"}},"elements":{"0":{"type":"html/text","style":{"width":"550px","line-height":"28px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlciI+PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMjRweDtjb2xvcjogIzQ0NDQ0NCI+WW91IGhhdmUgd29uIGEgbHVja3kgY291cG9uITwvc3Bhbj48L3A+"},"attrs":{"data-center_on_mobile":""},"childStyle":{}}}}}},"2":{"props":{"style_outer":{"padding":"10px 35px","background-image":"none","background-color":"#ffffff","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"530px"}},"elements":{"0":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHA+RGVhciB7d3Bsd2xfY3VzdG9tZXJfbmFtZX0sPC9wPg=="},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"1":{"type":"html/spacer","style":{"width":"530px"},"content":{},"attrs":{},"childStyle":{".emtmpl-spacer":{"padding":"18px 0px 0px"}}},"2":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHA+WW91IGhhdmUgd29uIGEgZGlzY291bnQgY291cG9uIGJ5IHNwaW5uaW5nIGx1Y2t5IHdoZWVsIG9uIG15IHdlYnNpdGUuIFBsZWFzZSBhcHBseSB0aGUgY291cG9uIHdoZW4gc2hvcHBpbmcgd2l0aCB1cy48L3A+"},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"3":{"type":"html/button","style":{"width":"530px","font-size":"15px","font-weight":"400","color":"#1de712","line-height":"22px","text-align":"center","padding":"20px 0px 20px 1px"},"content":{"text":"e3dwbHdsX3ByaXplX3ZhbHVlfQ=="},"attrs":{"href":"{shop_url}"},"childStyle":{"a":{"border-width":"2px","border-radius":"0px","border-color":"#162447","border-style":"dashed","background-color":"#ffffff","width":"200px"}}},"4":{"type":"html/spacer","style":{"width":"530px"},"content":{},"attrs":{},"childStyle":{".emtmpl-spacer":{"padding":"18px 0px 0px"}}},"5":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHA+WW91cnMgc2luY2VyZWx5ITwvcD4KPHA+e3dwbHdsX3NpdGVfdGl0bGV9PC9wPg=="},"attrs":{"data-center_on_mobile":""},"childStyle":{}}}}}},"3":{"props":{"style_outer":{"padding":"25px 35px","background-image":"none","background-color":"#162447","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"100%"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444","width":"530px"}},"elements":{"0":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlciI+PHNwYW4gc3R5bGU9ImNvbG9yOiAjZjVmNWY1O2ZvbnQtc2l6ZTogMjBweCI+R2V0IGluIFRvdWNoPC9zcGFuPjwvcD4="},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"1":{"type":"html/social","style":{"width":"530px","text-align":"center","padding":"20px 0px 0px","background-image":"none"},"content":{},"attrs":{"facebook":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/fb-blue-white.png","facebook_url":"#","twitter":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/twi-cyan-white.png","twitter_url":"#","instagram":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/ins-white-color.png","instagram_url":"#","youtube":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/yt-color-white.png","youtube_url":"","linkedin":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/li-color-white.png","linkedin_url":"","whatsapp":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/wa-color-white.png","whatsapp_url":"","telegram":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/tele-color-white.png","telegram_url":"","tiktok":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/tiktok-color-white.png","tiktok_url":"","pinterest":"{_site_url}/wp-content/plugins/9mail-wordpress-email-templates-designer/assets/img/pin-color-white.png","pinterest_url":"","direction":"","data-width":""},"childStyle":{}},"2":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"20px 0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlciI+PHNwYW4gc3R5bGU9ImNvbG9yOiAjZjVmNWY1O2ZvbnQtc2l6ZTogMTJweCI+VGhpcyBlbWFpbCB3YXMgc2VudCBieSA6IDxzcGFuIHN0eWxlPSJjb2xvcjogI2ZmZmZmZiI+PGEgc3R5bGU9ImNvbG9yOiAjZmZmZmZmIiBocmVmPSJ7YWRtaW5fZW1haWx9Ij57YWRtaW5fZW1haWx9PC9hPjwvc3Bhbj48L3NwYW4+PC9wPgo8cCBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyIj48c3BhbiBzdHlsZT0iY29sb3I6ICNmNWY1ZjU7Zm9udC1zaXplOiAxMnB4Ij5Gb3IgYW55IHF1ZXN0aW9ucyBwbGVhc2Ugc2VuZCBhbiBlbWFpbCB0byA8c3BhbiBzdHlsZT0iY29sb3I6ICNmZmZmZmYiPjxhIHN0eWxlPSJjb2xvcjogI2ZmZmZmZiIgaHJlZj0ie2FkbWluX2VtYWlsfSI+e2FkbWluX2VtYWlsfTwvYT48L3NwYW4+PC9zcGFuPjwvcD4="},"attrs":{"data-center_on_mobile":""},"childStyle":{}},"3":{"type":"html/text","style":{"width":"530px","line-height":"22px","background-image":"none","padding":"0px","border-left-width":"0px","border-top-width":"0px","border-right-width":"0px","border-bottom-width":"0px","border-radius":"0px","border-color":"#444444"},"content":{"text":"PHAgc3R5bGU9InRleHQtYWxpZ246IGNlbnRlciI+PHNwYW4gc3R5bGU9ImNvbG9yOiAjZjVmNWY1Ij48c3BhbiBzdHlsZT0iY29sb3I6ICNmNWY1ZjUiPjxzcGFuIHN0eWxlPSJmb250LXNpemU6IDEycHgiPjxhIHN0eWxlPSJjb2xvcjogI2Y1ZjVmNSIgaHJlZj0iIyI+UHJpdmFjeSBQb2xpY3k8L2E+Jm5ic3A7IHwmbmJzcDsgPGEgc3R5bGU9ImNvbG9yOiAjZjVmNWY1IiBocmVmPSIjIj5IZWxwIENlbnRlcjwvYT48L3NwYW4+PC9zcGFuPjwvc3Bhbj48L3A+"},"attrs":{"data-center_on_mobile":""},"childStyle":{}}}}}}}}';
        $data = str_replace('{_site_url}',get_site_url(), $data);
		$samples['wplwl_coupon_email'] = [
			'basic' => [
				'name' => esc_html__( 'Basic', 'wordpress-lucky-wheel' ),
				'data'=>$data
			],
		];
		return $samples;
	}

	public function register_render_preview_shortcode( $sc ) {
		$date_format = get_option( 'date_format', 'F d, Y' );
		if ( ! $date_format ) {
			$date_format = 'F d, Y';
		}

		$sc['{wplwl_prize_label}']     = '10% OFF';
		$sc['{wplwl_site_title}']      = get_bloginfo( 'name' );
		$sc['{wplwl_prize_value}']     = 'LUCKY_WHEEL';
		$sc['{wplwl_customer_name}']   = 'John';
		$sc['{wplwl_customer_email}']  = 'johndoe@villatheme.com';
		$sc['{wplwl_customer_mobile}'] = '012345678910';

		return $sc;
	}
    public static function send_mail($id,$user_email,$replace=[]){
        if (!$id ||!$user_email || !self::$is_active){
            return '';
        }
	    $post = get_post( $id );
	    if ( ! $post || !in_array($post->post_type ,[ 'emtmpl','wp_email_tmpl'])) {
		    return '';
	    }
	    $email_render = EmTmpl\Inc\Email_Render::instance( [ 'template_id' => $id ] );
	    ob_start();
	    $email_render->render();
	    $content = ob_get_clean();
	    $custom_style = $email_render->custom_style();
        if ($content) {
	        $content = str_replace( '[custom_style]', $custom_style, $content );
        }
	    $attachments = [];
	    $files       = get_post_meta( $id, 'emtmpl_attachments', true );
	    if ( ! empty( $files ) && is_array( $files ) ) {
		    foreach ( $files as $file_id ) {
			    $attachments[] = get_attached_file( $file_id );
		    }
	    }
	    $subject = $email_render->replace_shortcode( $post->post_title);
	    if (!empty($replace['search'])) {
		    $content = str_replace($replace['search'],$replace['replace']??'',$content);
		    $subject = str_replace($replace['search'],$replace['replace']??'',$subject);
	    }
	    $header       = 'Content-Type: text/html; charset=utf-8;';
	    wp_mail( $user_email, $subject, $content.'ignore_9mail', $header, $attachments );
        return true;
    }
}