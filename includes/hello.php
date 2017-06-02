<?php
/**
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Process HTTP HEAD 'Hello' message which is sent by Wordapp Platform to discover if plugin is installed
 *
 * @var string X_WA_PDX_HELLO       request, Hello Message HTTP header
 * @var string X-WA-PDX-VERSION     response, Plugin version HTTP header
 * @var string X-WA-PDX-AJAX-URL    response, Plugin AJAX Url HTTP header
 * @var string X-WA-PDX-WP-VERSION  response, WordPress Version HTTP header
 */

function wa_pdx_hello()
{
    if($_SERVER['REQUEST_METHOD'] === 'HEAD')
    {
        if (!empty($_SERVER['HTTP_X_WA_PDX_HELLO']))
        {
            global $wp_version;
            $admin_ajax_url = admin_url('admin-ajax.php');
            header('X-WA-PDX-VERSION: ' . PDX_PLUGIN_VERSION_NUMBER);
            header('X-WA-PDX-AJAX-URL: ' . $admin_ajax_url);
            header('X-WA-PDX-WP-VERSION: ' . $wp_version);
            if (PDX_LOG_ENABLE)
            {
                $log  = "Catched Wordapp Hello Message:\nX-WA-PDX-HELLO: ".$_SERVER['HTTP_X_WA_PDX_HELLO']."\n";
                $log .= "Response headers added:\n";
                $log .= 'X-WA-PDX-VERSION: ' . PDX_PLUGIN_VERSION_NUMBER . "\n";
                $log .= 'X-WA-PDX-AJAX-URL: ' . $admin_ajax_url . "\n";
                $log .= 'X-WA-PDX-WP-VERSION: ' . $wp_version . "\n";
                file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
            }
        }
    }
}
