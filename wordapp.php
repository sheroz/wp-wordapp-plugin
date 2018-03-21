<?php
/**
 * Wordapp Plugin for WordPress.
 *
 * @link              http://wordapp.io
 * @since             1.0.0
 * @package           Wordapp
 *
 * @wordpress-plugin
 * Plugin Name:       Wordapp
 * Plugin URI:        https://wordpress.org/plugins/wordapp/
 * Description:       Wordapp is a language-processing platform for SEO and SEM. Wordapp plugin connects your site with Wordapp Platform to create, translate and optimize online content easily and seamlessly.
 * Version:           1.2.9
 * Author:            Wordapp
 * Author URI:        http://wordapp.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordapp
 */

require plugin_dir_path( __FILE__ ) . 'includes/pdx.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Seems at this stage it is not practical to clean configuration after uninstalling,
// otherwise we need to configure plugins again
// register_activation_hook( __FILE__ , 'wa_pdx_config_clear' );
// register_deactivation_hook( __FILE__ , 'wa_pdx_config_clear' );

add_action( 'init', 'wa_pdx_hello' );
add_action( 'wp_ajax_wa_pdx', 'ajax_wa_pdx' );
add_action( 'wp_ajax_nopriv_wa_pdx', 'ajax_wa_pdx' );
add_filter( 'pre_get_posts', 'wa_pdx_filter_pre_get_posts' );
wa_pdx_load_admin(plugin_basename( __FILE__ ));
