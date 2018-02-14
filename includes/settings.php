<?php
/**
 * Wordapp Plugin Settings Class
 *
 * @since   1.2.7
 * @package Wordapp
 */
namespace wa_pdx;

defined( 'ABSPATH' ) or exit;

/**
 * Wordapp Plugin settings class.
 *
 * @since   1.2.7
 * @package Wordapp
 */
class Settings {


	/**
	 * This plugin's settings page slug.
	 *
	 * @since  1.2.7
	 * @access private
	 * @var    string
	 */
	private static $page = 'wa_pdx';

	/**
	 * This plugin's option name.
	 *
	 * @since  1.2.7
	 * @access private
	 * @var    string
	 */
	private static $option_name = 'wa_pdx_options';

	/**
	 * This plugin's option group name.
	 *
	 * @since  1.2.7
	 * @access private
	 * @var    string
	 */
	private static $option_group = 'wa_pdx_option_group';

	/**
	 * This plugin's options section name.
	 *
	 * @since  1.2.7
	 * @access private
	 * @var    string
	 */
	private static $section_name = 'wa_pdx_options_section';

	/**
	 * This plugin's defatuls options.
	 *
	 * @since  1.2.7
	 * @access private
	 * @var    array $options Default options.
	 */
	private static $default_options = array(
		'validate_signature' => true,
		'validate_ip' => true,
		'server_ip' => '54.246.232.229, 54.171.165.11, 52.16.70.35',
	);

	/**
	 * This plugin's settings options.
	 *
	 * @since  1.2.7
	 * @access private
	 * @var    array $options Array of this plugin settings options.
	 */
	private $options = array();


	/**
	 * Register settings and admin menu.
	 *
	 * @since 1.2.7
	 * @param array $options Plugin settings options.
	 */
	public function __construct() {

		$this->options = self::get_options();

		add_action('admin_menu', array( $this, 'register_settings_page'));

	}

	/**
	 * Returns options
	 *
	 * @since  1.2.7
	 * @return array
	 */
	public static function get_options() {

		$options = get_option(self::$option_name, array());

		return wp_parse_args($options, self::$default_options);
	}

	/**
	 * Plugin Settings menu.
	 *
	 * @since 1.2.7
	 */
	public function register_settings_page() {

		add_options_page(
			__( 'Wordapp', 'wordapp' ),
			__( 'Wordapp', 'wordapp' ),
			'manage_options',
			self::$page,
			array( $this, 'settings_page' )
		);

		add_action( 'admin_init', array( $this, 'register_settings'  ) );

	}


	/**
	 * Register plugin settings.
	 *
	 * @since 1.2.7
	 */
	public function register_settings() {

		register_setting(
			self::$option_name,
			self::$option_name,
			array( $this, 'sanitize_field' )
		);

		add_settings_section(
			self::$option_name,
			__( 'Wordapp Plugin Settings', 'wordapp' ),
			array( $this, 'settings_info' ),
			self::$page
		);

		$settings_fields = array(
			'validate_signature' => array(
				'label'    => esc_html__( 'Validate Signature', 'wordapp' ),
				'callback' => array( $this, 'print_validate_signature_field' ),
		   	),
			'validate_ip' => array(
				'label'    => esc_html__( 'Validate IP', 'wordapp' ),
				'callback' => array( $this, 'print_validate_ip_field' ),
			),
			'server_ip' => array(
				'label'    => esc_html__( 'Server IP address(es)', 'wordapp' ),
				'callback' => array( $this, 'print_server_ip_field' ),
		   	),
		);

		foreach ( $settings_fields as $key => $field ) {
			add_settings_field(
				self::$option_name . '['. $key . ']',
				$field['label'],
				$field['callback'],
				self::$page,
				self::$option_name
			);
		}

	}


	/**
	 * Settings page additional info.
	 * Prints more details on the plugin settings page.
	 *
	 * @since 1.2.7
	 */
	public function settings_info() {

	}

	/**
	 * Prints out 'Validate Signature field'
	 *
	 * @since 1.2.7
	 */
	public function print_validate_signature_field() {

		?>
		<input type="checkbox" id="wordapp_validate_signature" name="<?php echo self::$option_name; ?>[validate_signature]" value="1" <?php checked( (bool) $this->options['validate_signature'] ); ?> />
		<label for="wordapp_validate_signature"><?php esc_html_e( 'Yes', 'wordapp' ); ?></label><br>
		<p class="description"><?php esc_html_e( 'Tick this option if you want Wordapp Pluging to validate sender by digital signature check.', 'wordapp' ); ?></p>
		<?php

	}

	/**
	 * Prints out 'Validate IP field'
	 *
	 * @since 1.2.7
	 */
	public function print_validate_ip_field() {

		?>
		<input type="checkbox" id="wordapp_validate_ip" name="<?php echo self::$option_name; ?>[validate_ip]" value="1" <?php checked( (bool) $this->options['validate_ip'] ); ?> />
		<label for="wordapp_validate_ip"><?php esc_html_e( 'Yes', 'wordapp' ); ?></label><br>
		<p class="description"><?php esc_html_e( 'Tick this option if you want Wordapp Plugin to validate sender by IP address.', 'wordapp' ); ?></p>
		<?php
	}

	/**
	 * Settings page IP Range field.
	 *
	 * @since 1.2.7
	 */
	public function print_server_ip_field() {

		?>
		<input type="text" class="regular-text" id="wordapp_server_ip" name="<?php echo self::$option_name; ?>[server_ip]" value="<?php echo esc_attr( $this->options['server_ip'] ); ?>" />
		<label for="wordapp_server_ip"><?php esc_html_e( 'Server IP address(es) (optional)', 'wordapp' ); ?></label><br>
		<p class="description"><?php esc_html_e( 'You may specify any of the following, to give access to specific IPs to Wordapp Plugin:', 'wordapp' ); ?><br>
			<ol>
				<li><small><?php
					/* translators: Placeholders: %1$s - a single IP address */
					printf( __( 'An IP address (for example %1$s).', 'wordapp' ),
						'<code>192.168.50.4</code>'
					); ?></small></li>
				<li><small><?php
					/* translators: Placeholders: %1$s - comma separated IP addresses */
					printf( __( 'Comma separated (%1$s).', 'wordapp' ),
						'<code>192.168.10.25, 192.168.10.28</code>'
					); ?></small></li>
			</ol>
		</p>
		<?php

	}

	/**
	 * Sanitize user input in settings page.
	 *
	 * @since  1.2.7
	 * @param  array $option user input
	 * @return array sanitized input
	 */
	public function sanitize_field( $option ) {

		$input = wp_parse_args( $option, array(
			'validate_signature' => false,
			'validate_ip' => false,
			'server_ip' => '',
		) );

		$sanitized_input = array(
			'validate_signature' => ! empty( $input['validate_signature'] ),
			'validate_ip' => ! empty( $input['validate_ip'] ),
			'server_ip' => sanitize_text_field( $input['server_ip'] ),
		);

		return $sanitized_input;
	}


	/**
	 * Settings page.
	 *
	 * @since 1.2.7
	 */
	public function settings_page() {

		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Wordapp', 'wordapp' ); ?></h2>
			<hr />
			<form method="post" action="options.php">
				<?php

				settings_fields( self::$option_name );

				do_settings_sections( self::$page );

				submit_button();

				?>
			</form>
		</div>
		<?php

	}


}
