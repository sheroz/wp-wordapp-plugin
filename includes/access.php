<?php
/**
 * Remote access realted functions.
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
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_admin_access_url($params)
{

}

/**
 * Checks the admin access param and grants the required access.
 */
function wa_pdx_check_admin_access()
{
    if($_SERVER['REQUEST_METHOD'] === 'GET' && isset( $_GET['wa_pdx_access_ticket'] ) )
    {
        $access_ticket = sanitize_key($_GET['wa_pdx_access_ticket']);
        if( !empty($access_ticket) && $access_ticket.length() == 24)
        {
            $access_tickets = get_option('wa_pdx_access_tickets');
            if( !empty($access_tickets))
            {
                $access_params = $access_tickets[$access_ticket];
                if( !empty($access_params))
                {
                    $user_id = $access_params['user_id'];
                    $post_id = $access_params['post_id'];
                    $valid_before = $access_params['valid_before'];
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
