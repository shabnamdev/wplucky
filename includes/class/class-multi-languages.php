<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPLWL_Multi_Languages {
	public $cache = [];

	public function __construct() {
		add_filter( 'wplwl_preview_emails_button_ids', array( $this, 'preview_emails_button_ids' ), 10, 1 );
		add_filter( 'wplwl_update_settings_args', array( $this, 'update_settings_args' ), 10, 1 );
		add_filter( 'wplwl_before_option_field', array( $this, 'before_option_field' ), 10, 2 );
		add_filter( 'wplwl_after_option_field', array( $this, 'after_option_field' ), 10, 2 );
	}
    public function preview_emails_button_ids($arg){
        if (!is_array($arg)){
            $arg = [];
        }
	    $languages                      = $this->get_languages();
        if (is_array($languages) && !empty($languages)){
	        $tmp = $arg;
            foreach ($arg as $id){
	            foreach ( $languages as $language ) {
		           $tmp[] = $id . '_' . $language;
	            }
            }
	        $arg = array_unique($tmp);
        }
        return $arg;
    }

	public function update_settings_args( $args ) {
        if (!is_array($args)){
            $args = (array)$args;
        }
		$languages                      = $this->get_languages();
		if (is_array($languages) && !empty($languages)){
			if (!isset($args['result'])|| !is_array($args['result'])){
				$args['result'] = [];
			}
			if (!isset($args['wheel'])||!is_array($args['wheel'])){
				$args['wheel'] = [];
			}
			if (!isset($args['wheel_wrap'])||!is_array($args['wheel_wrap'])){
				$args['wheel_wrap'] = [];
			}
			if (!isset($args['mailchimp'])||!is_array($args['mailchimp'])){
				$args['mailchimp'] = [];
			}
			foreach ( $languages as $value ) {
				$args['result'][ 'email_' . $value ]            = array(
					'subject'     => isset( $_POST[ 'subject_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'subject_' . $value ] ) ) : "",
					'heading'     => isset( $_POST[ 'heading_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'heading_' . $value ] ) ) : "",
					'content'     => isset( $_POST[ 'content_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'content_' . $value ] ) ) : "",
					'footer_text' => isset( $_POST[ 'footer_text_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'footer_text_' . $value ] ) ) : "",
				);
				$args['result'][ 'notification_' . $value ]     = array(
					'win'  => isset( $_POST[ 'result_win_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'result_win_' . $value ] ) ) : "",
					'lost' => isset( $_POST[ 'result_lost_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'result_lost_' . $value ] ) ) : "",
				);
				$args['wheel'][ 'quantity_label_' . $value ]    = isset( $_POST[ 'quantity_label_' . $value ] ) ? sanitize_text_field( $_POST[ 'quantity_label_' . $value ] ) : '';
				$args['wheel'][ 'custom_label_' . $value ]      = isset( $_POST[ 'custom_type_label_' . $value ] ) ? array_map( 'sanitize_text_field', $_POST[ 'custom_type_label_' . $value ] ) : array();
				$args['wheel']['email_templates_' . $value ]      = isset( $_POST[ 'email_templates_' . $value ] ) ? array_map( 'sanitize_text_field', $_POST[ 'email_templates_' . $value ] ) : array();
				$args['wheel_wrap'][ 'spin_button_' . $value ]  = isset( $_POST[ 'wheel_wrap_spin_button_' . $value ] ) ? sanitize_text_field( $_POST[ 'wheel_wrap_spin_button_' . $value ] ) : '';
				$args['wheel_wrap'][ 'description_' . $value ]  = isset( $_POST[ 'wheel_wrap_description_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'wheel_wrap_description_' . $value ] ) ) : '';
				$args['wheel_wrap'][ 'gdpr_message_' . $value ] = isset( $_POST[ 'gdpr_message_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'gdpr_message_' . $value ] ) ) : '';
				$args['mailchimp'][ 'lists_' . $value ]         = isset( $_POST[ 'mailchimp_lists_' . $value ] ) ? sanitize_text_field( $_POST[ 'mailchimp_lists_' . $value ] ) : '';
				$args['metrilo_tag_' . $value ]         = isset( $_POST[ 'metrilo_tag_' . $value ] ) ? sanitize_text_field( $_POST[ 'metrilo_tag_' . $value ] ) : '';

			}
		}
        return $args;
	}
	public function apply_multi_language( $name, $param ) {
		$result = false;
		if ( in_array( $name, [
                'mailchimp_lists',
                'metrilo_tag',
                'quantity_label',
                'custom_type_label',
                'email_templates',
                'wheel_wrap_description',
                'wheel_wrap_spin_button',
                'gdpr_message',
                'result_win',
                'result_lost',
                'subject',
                'content',
                'footer_text',
                'heading',
            ] ) ) {
			$result = true;
		}

		return $result;
	}

	public function after_option_field( $name, $param=[] ) {
		if ( ! $this->apply_multi_language( $name, $param ) ) {
			return;
		}
		if ( ! isset( $param['default_value'] ) ) {
			$param['default_value'] = $param['value'] ?? '';
		}
		$languages                      = $this->get_languages();
		$settings                       = VI_WORDPRESS_LUCKY_WHEEL_DATA::get_instance();
		if ( is_array( $languages ) && !empty( $languages ) ) {
			foreach ( $languages as $language ) {
				$this->print_other_country_flag( $name, $language );
                $name_t = $name . '_' . $language;
				switch ( $name ) {
					case 'subject':
					case 'heading':
					case 'footer_text':
						$param['value'] =  $settings->get_params( 'result', 'email', $language )[$name]??'' ;
						ob_start();
						?>
                        <input id="<?php echo esc_attr( $name_t ) ?>" type="text"
                               name="<?php echo esc_attr( $name_t ) ?>"
                               value="<?php echo esc_attr( stripslashes( $param['value']) ); ?>">
						<?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'content':
						$editor_option = $param['editor_option'] ?? [];
						$param['value'] =  stripslashes( $settings->get_params( 'result', 'email', $language )['content']??'');
						ob_start();
						wp_editor( $param['value'] , $name_t, $editor_option);
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'result_lost':
						$result_lost_option = $param['result_lost_option'] ?? [];
						$param['value'] =  stripslashes( $settings->get_params( 'result', 'notification',  $language )['lost']??'');
						ob_start();
						wp_editor( $param['value'] , $name_t, $result_lost_option);
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'result_win':
						$result_win_option = $param['result_win_option'] ?? [];
						$param['value'] =  stripslashes( $settings->get_params( 'result', 'notification', $language )['win']??'');
						ob_start();
						wp_editor( $param['value'] , $name_t, $result_win_option);
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'gdpr_message':
						$gdpr_message_option = $param['gdpr_message_option'] ?? [];
						$param['value'] =  stripslashes( $settings->get_params( 'wheel_wrap', 'gdpr_message', $language ));
						ob_start();
						wp_editor( $param['value'] , $name_t, $gdpr_message_option);
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'wheel_wrap_spin_button':
						$param['value'] =  stripslashes( $settings->get_params( 'wheel_wrap', 'spin_button', $language ));
						ob_start();
						?>
                        <input id="<?php echo esc_attr( $language ) ?>"
                               type="text"
                               name="<?php echo esc_attr( $name_t ) ?>"
                               value="<?php echo esc_attr( stripslashes( $param['value']) ); ?>">
                        <?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'wheel_wrap_description':
                        $wheel_desc_option = $param['wheel_desc_option'] ?? [];
						$param['value'] =  stripslashes( $settings->get_params( 'wheel_wrap', 'description', $language ));
						ob_start();
						wp_editor( $param['value'] , $name_t, $wheel_desc_option);
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'email_templates':
						$email_templates_lang = $settings->get_params( 'wheel', 'email_templates', $language );
                        $wheel_slide_index = $param['wheel_slide_index'] ?? 0;
						$all_email_templates = $param['wheel_email_templates'] ?? [];
						$param['value'] = $email_templates_lang[$wheel_slide_index]??'';
						?>
                        <select class="vi-ui dropdown fluid" name="<?php echo esc_attr( "{$name_t}[]" ) ?>">
                            <option value="0"><?php esc_html_e( 'None', 'wordpress-lucky-wheel' ) ?></option>
							<?php
							if ( !empty( $all_email_templates ) ) {
								foreach ( $all_email_templates as $all_email_templates_v ) {
									?>
                                    <option value="<?php echo esc_attr( $all_email_templates_v->ID ); ?>" <?php selected( $all_email_templates_v->ID,
										$param['value'] ); ?>><?php echo esc_html( "(#{$all_email_templates_v->ID}){$all_email_templates_v->post_title}" ); ?></option>
									<?php
								}
							}
							?>
                        </select>
						<?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'custom_type_label':
						$custom_label_lang = $settings->get_params( 'wheel', 'custom_label', $language );
                        $wheel_slide_index = $param['wheel_slide_index'] ?? 0;
						$param['value'] = isset( $custom_label_lang[ $wheel_slide_index ] ) ? $custom_label_lang[ $wheel_slide_index ] : $param['default_value'] ;
						ob_start();
						?>
                        <input type="text"
                               name="<?php echo esc_attr( "{$name_t}[]" ) ?>"
                               class="custom_type_label"
                               value="<?php echo esc_attr( $param['value']  ); ?>"
                               placeholder="Label"/>
						<?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'quantity_label':
						$param['value'] = $settings->get_params( 'wheel', 'quantity_label', $language ) ;
						ob_start();
						?>
                        <input type="text" name="<?php echo esc_attr( $name_t ); ?>"
                               id="<?php echo esc_attr( $name_t ); ?>"
                               value="<?php echo esc_attr( stripslashes($param['value'] )) ?>">
						<?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'metrilo_tag':
						$param['value'] = $settings->get_params( 'metrilo_tag', '', $language );
						ob_start();
						?>
                        <input type="text" name="<?php echo esc_attr( $name_t ); ?>"
                               id="<?php echo esc_attr( $name_t ); ?>"
                               value="<?php echo esc_attr( $param['value'] ) ?>">
						<?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
					case 'mailchimp_lists':
						$param['value'] = $settings->get_params( 'mailchimp', 'lists', $language );
						$mailchimp_list = $param['mailchimp_list'] ?? [];
						ob_start();
						?>
                        <select class="select-who vi-ui fluid dropdown"
                                name="<?php echo esc_attr( $name_t ); ?>"
                                id="<?php echo esc_attr( $name_t ); ?>">
							<?php

							if ( is_array( $mailchimp_list ) && count( $mailchimp_list ) ) {
								foreach ( $mailchimp_list as $mail_list ) {
                                    if (empty($mail_list->id)){
                                        continue;
                                    }
									echo "<option value='" . esc_attr( $mail_list->id ) . "' " . selected( $param['value'], $mail_list->id ) . ">" . esc_html( $mail_list->name ?? $mail_list->id) . "</option>";
								}
							}
							?>
                        </select>
						<?php
						$param['html'] = ob_get_clean();
						$settings::villatheme_render_field( $name_t, $param );
						break;
				}
			}
		}
	}

	public function before_option_field( $name, $param ) {
		if ( ! $this->apply_multi_language( $name, $param ) ) {
			return;
		}
		$this->print_default_country_flag();
	}

	public function map_update_settings_args( $args ) {
		$languages = $this->get_languages();
		if ( ! is_array( $args ) ) {
			$args = [];
		}
        if (is_array( $languages ) && empty( $languages ) ) {
	        if ( ! isset( $args['field'] ) ) {
		        $args['field'] = [];
	        }
	        foreach ( $languages as $language ) {
		        $args['field'][] = 'pos_endpoint' . '_' . $language;
	        }
        }

		return $args;
	}

	public function print_default_country_flag() {
		$languages        = $this->get_languages();
		$languages_data   = $this->get_languages_data();
		$default_language = $this->get_default_language();
		if ( count( $languages ) ) {
			?>
            <p>
                <label><?php
					if ( isset( $languages_data[ $default_language ]['country_flag_url'] ) && $languages_data[ $default_language ]['country_flag_url'] ) {
						?>
                        <img src="<?php echo esc_url( $languages_data[ $default_language ]['country_flag_url'] ); ?>">
						<?php
					}
					echo esc_html( $default_language );
					if ( isset( $languages_data[ $default_language ]['translated_name'] ) ) {
						echo esc_html( '(' . $languages_data[ $default_language ]['translated_name'] . '):' );
					}
					?></label>
            </p>
			<?php
		}
	}

	public function print_other_country_flag( $param, $lang, $tag = 'p', $echo_lang = true, $echo = true ) {
		if ( ! $lang ) {
			return '';
		}
		$languages_data = $this->get_languages_data();
		if ( ! $echo ) {
			ob_start();
		}
		printf( '<%s>', esc_attr( $tag ) );
		?>
        <label for="<?php echo esc_attr( "{$param}_{$lang}" ); ?>"><?php
			if ( ! empty( $languages_data[ $lang ]['country_flag_url'] ) ) {
				?>
                <img src="<?php echo esc_url( $languages_data[ $lang ]['country_flag_url'] ); ?>">
				<?php
			}
			if ( $echo_lang ) {
				echo wp_kses_post( $lang );
				if ( isset( $anguages_data[ $lang ]['translated_name'] ) ) {
					echo wp_kses_post( '(' . $languages_data[ $lang ]['translated_name'] . ')' );
				}
				echo esc_html( ' : ' );
			}
			?></label>
		<?php
		printf( '</%s>', esc_attr( $tag ) );
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	public function get_languages() {
		if ( isset( $this->cache['languages'] ) ) {
			return $this->cache['languages'];
		}
		$default_language = $this->get_default_language();
		$languages_data   = $this->get_languages_data();
		$languages        = [];
		if ( is_array( $languages_data ) && count( $languages_data ) ) {
			foreach ( $languages_data as $key => $language ) {
				if ( $key != $default_language ) {
					$languages[] = $key;
				}
			}
		}
		$this->cache['languages'] = $languages;

		return $this->cache['languages'];
	}

	abstract protected function get_languages_data();

	abstract protected function get_current_language();

	abstract protected function get_default_language();
}