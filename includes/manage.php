<?php
/**
 * Management related functions
 *
 * @author      Sheroz Khaydarov http://sheroz.com
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2019
 * @link        https://github.com/sheroz/wp-wordapp-plugin
 * @since       1.4.0
 */

/**
 * Upgrades & activates plugin
 
 * @api
 * @since       1.4.0
 * 
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_plugin_upgrade ($plugin = PDX_PLUGIN_PATH )
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $is_active = is_plugin_active($plugin);

    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();
    $upgrader_result = $upgrader->upgrade( $plugin );
    if ( $upgrader_result ) {
        if ( $is_active ) {
            $activate_result = activate_plugin( $plugin );
            if ( is_wp_error( $activate_result ) ) 
                wa_pdx_send_response('Could not activate plugin');
            else 
                wa_pdx_send_response('Plugin upgraded and activated', true);
        }
        else wa_pdx_send_response('Plugin upgraded', true);
    }
    else wa_pdx_send_response('Could not upgrade plugin');
}
