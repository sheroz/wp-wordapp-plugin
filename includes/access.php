<?php
/**
 * Admin access related functions.
 *
 * @author      Sheroz Khaydarov http://sheroz.com
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2019
 * @link        https://github.com/sheroz/wp-wordapp-plugin
 * @since       1.3.7
 */

/**
 * Get admin access URL operation.
 *
 * @api
 *
 * @param array $params Operation parameters passed from Wordapp Platform.
 *              user_id - the user Id to be logged as
 *              post_id - the post Id to be redirected to after successful login
 *                        post_id = -1 for access to admin panel
 *              ticket  - the ticket for admin access
 *              expire  - the ticket expire time, in seconds
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_admin_access_url ($params)
{
    $user_id   = $params['user_id'];
    $post_id   = $params['post_id'];
    $ticket    = $params['ticket'];
    $expire    = $params['expire'];
    $access_tickets = get_option('wa_pdx_access_tickets');
    if( empty($access_tickets) )
    {
        $access_tickets = array();
    }
    $access_tickets[$ticket] = array(
        'user_id'   => $user_id,
        'post_id'   => $post_id
    );
    update_option('wa_pdx_access_tickets', $access_tickets);
    $admin_access_url = wa_pdx_add_url_params (get_home_url(), "wa_pdx_access_ticket=$ticket");
    $response_data = array('admin_access_url' => $admin_access_url);
    wa_pdx_send_response($response_data, true);
}

/**
 * Checks the admin access ticket and grants the required access.
 */
function wa_pdx_check_admin_access()
{
    if($_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['wa_pdx_access_ticket'] ) )
    {
        $ticket = sanitize_key($_GET['wa_pdx_access_ticket']);
        if( strlen($ticket) == 64)
        {
            $access_tickets = get_option('wa_pdx_access_tickets');
            if( $access_tickets )
            {
                $access_params = $access_tickets[$ticket];
                if( !empty($access_params))
                {
                    $user_id = $access_params['user_id'];
                    $post_id = $access_params['post_id'];
                    $valid_before = $access_params['valid_before'];
                    unset($access_tickets[$ticket]);
                    update_option('wa_pdx_access_tickets', $access_tickets);
                    $user = get_user_by('id', $user_id);
                    if( $user ) {
                        wp_set_auth_cookie( $user->ID );
                        wp_redirect( get_home_url() );
                    }
                }
            }
        }
	}	
}
