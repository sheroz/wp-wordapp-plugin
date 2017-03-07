<?php

/**
 *
 * @link              http://wordapp.io
 * @since             0.0.1
 * @package           Wordapp_Seo
 *
 * @wordpress-plugin
 * Plugin Name:       Wordapp SEO
 * Plugin URI:        http://wordapp.io/plugins/wordpress-seo
 * Description:       Wordapp is a language-processing platform for SEO and SEM. Wordapp SEO plugin connects your site with Wordapp Platform to create, translate and optimize online content easily and seamlessly.
 * Version:           0.0.1
 * Author:            Wordapp
 * Author URI:        http://wordapp.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordapp-seo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordapp-seo-activator.php
 */
function activate_wordapp_seo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordapp-seo-activator.php';
	Wordapp_Seo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordapp-seo-deactivator.php
 */
function deactivate_wordapp_seo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordapp-seo-deactivator.php';
	Wordapp_Seo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wordapp_seo' );
register_deactivation_hook( __FILE__, 'deactivate_wordapp_seo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordapp-seo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wordapp_seo() {

	$plugin = new Wordapp_Seo();
	$plugin->run();

}
run_wordapp_seo();
