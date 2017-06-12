<?php
/**
 * Wordapp plugin discovery related functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Processes HTTP HEAD 'Hello' message.
 * This message is used by Wordapp Platform to discover if Wordapp plugin is installed
 *
 * @param string X_WA_PDX_HELLO        HTTP request header, Hello Message Identifier
 * @return string X-WA-PDX-VERSION     HTTP response header, Plugin version
 * @return string X-WA-PDX-AJAX-URL    HTTP response header, Plugin AJAX entry point url
 * @return string X-WA-PDX-WP-VERSION  HTTP response header, WordPress Version
 */
function wa_pdx_hello()
{
    if($_SERVER['REQUEST_METHOD'] === 'HEAD')
    {
        if (!empty($_SERVER['HTTP_X_WA_PDX_HELLO']))
        {
            global $wp_version;
            $admin_ajax_url = admin_url('admin-ajax.php');
            header('X-WA-PDX-AJAX-URL: ' . $admin_ajax_url);
            header('X-WA-PDX-VERSION: ' . PDX_PLUGIN_VERSION_NUMBER);
            header('X-WA-PDX-WP-VERSION: ' . $wp_version);
            if (PDX_LOG_ENABLE)
            {
                $log  = "Catched Wordapp Hello Message:\nX-WA-PDX-HELLO: ".$_SERVER['HTTP_X_Wa_Pdx_Hello']."\n";
                $log .= "Response headers added:\n";
                $log .= 'X-Wa-Pdx-Version: ' . PDX_PLUGIN_VERSION_NUMBER . "\n";
                $log .= 'X-Wa-Pdx-Ajax-Url: ' . $admin_ajax_url . "\n";
                $log .= 'X-Wa-Pdx-Wp-Version: ' . $wp_version . "\n";
                file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
            }
        }
    }
}
