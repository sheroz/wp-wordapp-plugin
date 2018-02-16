<?php
/**
 * Common functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Generates random hex string by given length.
 *
 * @param int $len Required random string length.
 *
 * @return string Random hex string.
 */
function wa_pdx_random_hex_string($len)
{
    $chars = '0123456789abcdef';
    $chars_len = strlen($chars);
    $res = '';
    for ($pos = 0; $pos < $len; $pos++)
        $res .= $chars[rand(0, $chars_len - 1)];
    return $res;
}

/**
 * Sends JSON response and closes HTTP request.
 *
 * @param mixed $msg    Value of the 'data' or 'error' field in JSON response.
 *                      When $success = true then 'data' = $msg.
 *                      When $success = false then 'error' = $msg.
 *
 * @param bool $success The 'success' field in JSON response.
 *
 * @return void
 */
function wa_pdx_send_response ($msg, $success = false)
{
    $response['success'] = $success;
    if ($success)
        $response['data'] = $msg;
    else
        $response['error'] = $msg;

    wp_send_json($response);
}

/**
 * Gets users list.
 *
 * @api
 *
 * @return array Users list.
 */
function wa_pdx_op_user_list() {
    $users = get_users();
    wa_pdx_send_response($users, true);
}

/**
 * Adds query parameter(s) to url.
 *
 * @param string $url The Url to add query parameter(s)
 * @param array|string The query parameter(s) to add
 *
 * @return string The altered url.
 */
function wa_pdx_add_url_params ($url, $params)
{
    if (empty($params))
        return $url;

    $query = '?';
    if (strpos($url, $query) !== false)
        $query = '&';

    if (is_array($params))
    {
        $prefix = '';
        foreach ($params as $param)
        {
            $query .= $prefix . $param;
            $prefix = '&';
        }
    }
    else
        $query .= $params;

    return $url . $query;
}

/**
 * Loads the settings page of Wordapp Plugin
 *
 * @param  string $plugin_name
 * @return void
 */
function wa_pdx_load_admin($plugin_name) {

	if ( ! defined( 'DOING_AJAX' ) && is_admin() ) {

		// Add a settings link to the plugins admin screen.
		add_filter( "plugin_action_links_{$plugin_name}", function( $actions ) {
			return array_merge( array(
				'<a href="' . esc_url( admin_url( 'options-general.php?page=wa_pdx' ) ) . '">' . __( 'Settings', 'wordapp' ) . '</a>',
			), $actions );
		} );

		add_action('admin_menu', 'wa_pdx_register_settings_page');
	}
}
