<?php
/**
 * Plugin management related functions
 *
 * @author      Sheroz Khaydarov http://sheroz.com
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2019
 * @link        https://github.com/sheroz/wp-wordapp-plugin
 * @since       1.4.0
 */

include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
class Pdx_Silent_Skin extends \WP_Upgrader_Skin {
    public function feedback($string, ...$args) { /* no output */ }
}

/**
 * Upgrade plugin
 
 * @api
 * @since       1.4.0
 * 
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_plugin_upgrade ($params)
{
    $plugin = $params['plugin'];
    if (empty($plugin))
    {
        $plugin = PDX_PLUGIN_PATH;
    }

    ob_start();
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $is_active = is_plugin_active($plugin);

    $silent_skin =  new Pdx_Silent_Skin();  
    $upgrader = new Plugin_Upgrader( $silent_skin );
    $upgrader_result = $upgrader->upgrade( $plugin );
    if ( $upgrader_result ) {
        if ( $is_active ) {
            $activate_result = activate_plugin( $plugin );
            ob_end_clean();
            if ( is_wp_error( $activate_result ) ) {
                wa_pdx_send_response('could_not_activate_plugin');
            }
            else {
                wa_pdx_send_response('plugin_upgraded_and_activated', true);
            }
        }
        else {
            ob_end_clean();
            wa_pdx_send_response('plugin_upgraded', true);
        } 
    }
    else {
        ob_end_clean();
        wa_pdx_send_response('could_not_upgrade_plugin');
    } 
}

/**
 * Install plugin
 
 * @api
 * @since       1.4.1
 * 
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_plugin_install ($params)
{
    $plugin = $params['plugin'];
    if (empty($plugin))
    {
        wa_pdx_send_response('plugin_parameter_empty');
        return;
    }

    ob_start();
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $silent_skin =  new Pdx_Silent_Skin();  
    $upgrader = new Plugin_Upgrader( $silent_skin );
    $upgrader_result = $upgrader->install( $plugin );
    ob_end_clean();
    if ( $upgrader_result ) {
        wa_pdx_send_response('plugin_installed', true);
    }
    else {
        wa_pdx_send_response('plugin_install_failed', false);
    } 
}

/**
 * Activate plugin
 
 * @api
 * @since       1.4.1
 * 
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_plugin_activate ($params)
{
    $plugin = $params['plugin'];
    if (empty($plugin))
    {
        wa_pdx_send_response('plugin_parameter_empty');
        return;
    }

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $is_active = is_plugin_active($plugin);
    if ( !$is_active ) {
        $activate_result = activate_plugin( $plugin );
        if ( is_wp_error( $activate_result ) ) {
            wa_pdx_send_response('could_not_activate_plugin');
        }
        else {
            wa_pdx_send_response('plugin_activated', true);
        }
    }
    else {
        wa_pdx_send_response('plugin_already_active', true);
    } 
}

/**
 * Deactivate plugin
 
 * @api
 * @since       1.4.1
 * 
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_plugin_deactivate ($params)
{
    $plugin = $params['plugin'];
    if (empty($plugin))
    {
        wa_pdx_send_response('plugin_parameter_empty');
        return;
    }

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $is_active = is_plugin_active($plugin);
    if ( $is_active ) {
        $deactivate_result = deactivate_plugins( $plugin, true );
        if ( is_wp_error( $deactivate_result ) ) {
            wa_pdx_send_response('could_not_deactivate_plugin');
        }
        else {
            wa_pdx_send_response('plugin_deactivated', true);
        }
    }
    else {
        wa_pdx_send_response('plugin_not_active');
    } 
}

/**
 * Delete plugin
 
 * @api
 * @since       1.4.1
 * 
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_plugin_delete ($params)
{
    $plugin = $params['plugin'];
    if (empty($plugin))
    {
        wa_pdx_send_response('plugin_parameter_empty');
        return;
    }

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $delete_result = delete_plugins(array($plugin));
    if ( is_wp_error( $delete_result ) ) {
        wa_pdx_send_response('could_not_delete_plugin');
    }
    else {
        wa_pdx_send_response('plugin_deleted', true);
    }
}

/**
 * List installed plugins
 * 
 * @api
 * @since       1.4.1
 * 
 * @param none 
 * 
 * @return array 
 **/
function wa_pdx_plugin_list ()
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugins = get_plugins();
    wa_pdx_send_response($plugins, true);
}

