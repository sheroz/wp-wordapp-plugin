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
 * Generates random hex string.
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
 * Send a JSON response back to an AJAX request, and die().
 *
 * @param mixed $msg    The 'data' or 'error' field in JSON response.
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
 * Finds post by url
 *
 * @param string $post_url The url to find.
 *
 * @return int|null The post id found.
 */
function wa_pdx_find_post_by_url ($post_url)
{
    if(empty($post_url))
        return null;

    $posts = wa_pdx_get_posts();
    foreach ($posts as $post)
        if ($post['url'] == $post_url)
            return $post['id'];

    return null;
}

/**
 * @api
 *
 * API Get User List
 *
 * @return int|null The post id found.
 */
function wa_pdx_op_user_list() {
    $users = get_users();
    wa_pdx_send_response($users, true);
}

/**
 * @internal
 *
 * Retrieves list of posts matching criteria.
 *
 * @param array $params The query parameters. See https://developer.wordpress.org/reference/classes/wp_query/parse_query/
 *
 * @return array The posts found.
 */
function wa_pdx_get_posts($params)
{

    $args = array();
    if (empty($params))
    {
        $args['posts_per_page'] =  -1;
        $args['orderby'] = array('type','ID');
        $args['post_type'] = get_post_types();
        $args['post_status'] = get_post_stati();
    } else {
        // todo: parse params and add to $args
    }
//  'post_status' => 'publish,pending,draft,auto-draft,future,private,inherit,trash'

    $data = array ();

    $posts = get_posts($args);
    foreach ( $posts as $post ) {
        $data[] = array(
            'id'        => $post->ID,
            'url'       => get_permalink( $post->ID ),
            'type'      => $post->post_type,
            'title'     => $post->post_title,
            'status'    => get_post_status( $post->ID )
        );
    }
    wp_reset_postdata();
    return $data;
}

/**
 * @internal
 *
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
