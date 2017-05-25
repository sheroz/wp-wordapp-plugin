<?php

/**
 *
 * @link              http://wordapp.io
 * @since             0.0.1
 * @package           Wordapp_Seo
 *
 * @wordpress-plugin
 * Plugin Name:       Wordapp
 * Plugin URI:        http://wordapp.io/plugins/wp
 * Description:       Wordapp is a language-processing platform for SEO and SEM. Wordapp plugin connects your site with Wordapp Platform to create, translate and optimize online content easily and seamlessly.
 * Version:           0.4.4
 * Author:            Wordapp
 * Author URI:        http://wordapp.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordapp-seo
 * Domain Path:       /languages
 */

require plugin_dir_path( __FILE__ ) . 'includes/wa-api-pdx.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-wordapp-seo.php';

function wa_pdx_activate() {
    wa_pdx_clear_config();
}

function wa_pdx_deactivate() {
    wa_pdx_clear_config();
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook( __FILE__, 'wa_pdx_activate' );
register_deactivation_hook( __FILE__, 'wa_pdx_deactivate' );

$plugin = new Wordapp_Seo();
$plugin->run();
