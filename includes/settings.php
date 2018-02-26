<?php

/**
 * Returns options.
 *
 * @return array Options array stored in the database if PDX_SETTINGS_OPTION_NAME option_name exists.
 * Otherwise this function returns an array of default options.
 *
 */
function wa_pdx_get_options(){
	$options = get_option(PDX_SETTINGS_OPTION_NAME, array());

	return wp_parse_args($options, array(
		'validate_signature' => PDX_CONFIG_VALIDATE_SIGNATURE,
		'validate_ip' => PDX_CONFIG_VALIDATE_IP,
		'server_ip' => PDX_CONFIG_SERVER_IP,
	));
}

/**
 * Registers settings page.
 *
 * @return void
 */
function wa_pdx_register_settings_page(){
	add_options_page(
		__( 'Wordapp', 'wordapp' ),
		__( 'Wordapp', 'wordapp' ),
		'manage_options',
		PDX_SETTINGS_PAGE_SLUG,
		'wa_pdx_settings_page'
	);

	add_action( 'admin_init', 'wa_pdx_register_settings' );
}

/**
 * Registers settings, the section for the settings, and fields.
 *
 * @return void
 */
function wa_pdx_register_settings(){
	register_setting(
		PDX_SETTINGS_OPTION_NAME,
		PDX_SETTINGS_OPTION_NAME,
		'wa_pdx_sanitize_field'
	);

	add_settings_section(
		PDX_SETTINGS_OPTION_NAME,
		__( 'Settings', 'wordapp' ),
		'wa_pdx_print_settings_page_info',
		PDX_SETTINGS_PAGE_SLUG
	);

	$settings_fields = array(

		'connection_status' => array(
        'label' => esc_html__( 'Connection Status', 'wordapp' ),
        'callback' => 'wa_pdx_print_connection_status',
        ),
		'validate_signature' => array(
			'label' => esc_html__( 'Validate Signature', 'wordapp' ),
			'callback' => 'wa_pdx_print_validate_signature_field',
		),
		'validate_ip' => array(
			'label' => esc_html__( 'Validate IP', 'wordapp' ),
			'callback' => 'wa_pdx_print_validate_ip_field',
		),
		'server_ip' => array(
			'label' => esc_html__( 'Server IP List', 'wordapp' ),
			'callback' => 'wa_pdx_print_server_ip_field',
		),
	);

	foreach ( $settings_fields as $key => $field ) {
		add_settings_field(
			PDX_SETTINGS_OPTION_NAME . '['. $key . ']',
			$field['label'],
			$field['callback'],
			PDX_SETTINGS_PAGE_SLUG,
			PDX_SETTINGS_OPTION_NAME
		);
	}
}

/**
 * Prints out settings page info.
 *
 * @return void
 */
function wa_pdx_print_settings_page_info(){
	$logo_url = plugin_dir_url(dirname(__FILE__)) . 'img/wordapp-logo.svg';
	?>
	<p>
		<img src="<?php echo $logo_url; ?>" style="width: 40px; height: 40px; float: left; margin: 0 10px 7px 0;">
	<?php
	echo __('<a href="http://wordapp.io" target="_blank">Wordapp</a> is a language-processing platform for SEO and SEM.<br/>The Wordapp Plugin connects your site to the Wordapp Platform to allow you to create, translate and optimize online content easily and seamlessly.', 'wordapp');
	?>
	</p>
	<hr style="clear: both;" />
	<?php
}

/**
 * Prints out connection status.
 *
 * @return void
 */
function wa_pdx_print_connection_status() {
    $cfg = get_option(PDX_CONFIG_OPTION_KEY);
    if(empty($cfg)){
        ?>
        <span style="color: red;font-weight: bold;"><?php esc_html_e('Not connected', 'wordapp'); ?></span>
        <?php
    } else {
        ?>
        <span style="color: #82b15a;font-weight: bold;"><?php esc_html_e('Connected to Wordapp', 'wordapp'); ?></span>
        <?php
    }
}

/**
 * Prints out validate_signature field.
 *
 * @return void
 */
function wa_pdx_print_validate_signature_field(){
	$options = wa_pdx_get_options();
	?>
	<input type="checkbox" id="wordapp_validate_signature" name="<?php echo PDX_SETTINGS_OPTION_NAME; ?>[validate_signature]" value="1" <?php checked( (bool) $options['validate_signature'] ); ?> />
	<label for="wordapp_validate_signature"><?php esc_html_e( 'Yes', 'wordapp' ); ?></label><br>
	<p class="description"><?php esc_html_e( 'Tick this option to validate sender by digital signature check.', 'wordapp' ); ?></p>
	<?php
}

/**
 * Prints out validate_ip field.
 *
 * @return void
 */
function wa_pdx_print_validate_ip_field(){
	$options = wa_pdx_get_options();
	?>
	<input type="checkbox" id="wordapp_validate_ip" name="<?php echo PDX_SETTINGS_OPTION_NAME; ?>[validate_ip]" value="1" <?php checked( (bool) $options['validate_ip'] ); ?> />
	<label for="wordapp_validate_ip"><?php esc_html_e( 'Yes', 'wordapp' ); ?></label><br>
	<p class="description"><?php esc_html_e( 'Tick this option to validate sender by IP address.', 'wordapp' ); ?></p>
	<?php
}

/**
 * Prints out server_ip field.
 *
 * @return void
 */
function wa_pdx_print_server_ip_field(){
	$options = wa_pdx_get_options();
	$errors = get_settings_errors('wordapp_server_ip');
	$style = !empty($errors) && $errors[0]['code'] == 'wordapp_server_ip_error_invalid_ip' ? 'style="border: 2px solid red;"' : '';
	?>
	<textarea cols="17" rows="5" id="wordapp_server_ip" name="<?php echo PDX_SETTINGS_OPTION_NAME; ?>[server_ip]" <?php echo $style; ?> onfocus="this.style.border='none'"><?php echo esc_html_e( preg_replace('/\s+/', "\n", $options['server_ip']) ); ?></textarea>
	<p class="description">
		<small><?php esc_html_e('(Required when Validate IP is checked)', 'wordapp'); ?></small>
		<br>
		<?php esc_html_e( 'Enter one or more IP address that you want to allow access to the Wordapp Plugin.', 'wordapp' ); ?>
		<br>
		<?php esc_html_e( 'Write each IP address on a separate line.', 'wordapp' ); ?>
	</p>
	<?php
}

/**
 * Validate server_ip field and adds settings errors if there are any.
 *
 * @param  string  $field       Value of the server_ip field.
 *                              It should be converted to a space separated values list before passed to this function.
 * @param  boolean $validate_ip Value of the validate_ip field.
 * @return void
 */
function wa_pdx_validate_server_ip_field($field, $validate_ip = false){
	// server_ip field should not be empty when validate_ip field is checked
	$field = trim($field);

	if($validate_ip == true){
		if(empty($field)){
			add_settings_error(
				'wordapp_server_ip',
				esc_attr( 'wordapp_server_ip_error_empty_value' ),
				__('Warning: Server IPs list should not be empty when "Validate IP" option is checked.', 'wordapp'),
				'error'
			);
		}
	}

	if(!empty($field)){
		$ips = explode(' ', $field);
		foreach ($ips as $ip) {
			if(!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $ip)){
				add_settings_error(
					'wordapp_server_ip',
					esc_attr( 'wordapp_server_ip_error_invalid_ip' ),
					__('Warning: One or more IP addresses are invalid. Please fix it to validate IP addresses properly.', 'wordapp'),
					'error'
				);
				break;
			}
		}
	}
}

/**
 * Sanitizes server_ip field coming directly from user input.
 *
 * @param  string $str Value to be sanitized
 * @return string      Sanitized string
 */
function wa_pdx_sanitize_server_ip_field($str){
	$str = preg_replace('/\s{2,}/', ' ', trim($str));
	return sanitize_text_field($str);
}

/**
 * Sanitizes fields. This function is the callback that is passed to register_setting when PDX_SETTINGS_OPTION_NAME setting is registered.
 *
 * @param  array $option An array that holds the setting.
 * @return array         An array of sanitized values.
 */
function wa_pdx_sanitize_field($option){
	$input = wp_parse_args( $option, array(
		'validate_signature' => false,
		'validate_ip' => false,
		'server_ip' => '',
	) );

	$sanitized_input = array(
		'validate_signature' => ! empty( $input['validate_signature'] ),
		'validate_ip' => ! empty( $input['validate_ip'] ),
		'server_ip' => wa_pdx_sanitize_server_ip_field( $input['server_ip'] ), // todo trim?
	);

	wa_pdx_validate_server_ip_field($sanitized_input['server_ip'], $sanitized_input['validate_ip']);

	return $sanitized_input;
}

/**
 * Prints out the contents of the settings page of Wordapp Plugin.
 *
 * @return void
 */
function wa_pdx_settings_page(){
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Wordapp', 'wordapp' ); ?></h2>
		<hr />
		<!-- <pre>
		<?php
		$errors = get_settings_errors('wordapp_server_ip');
		print_r($errors);
		?>
		</pre> -->

		<form method="post" action="options.php">
			<?php

			settings_fields( PDX_SETTINGS_OPTION_NAME );

			do_settings_sections( PDX_SETTINGS_PAGE_SLUG );

			submit_button();

			?>
		</form>
	</div>
	<?php
}
