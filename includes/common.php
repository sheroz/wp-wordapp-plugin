<?php
/**
 * Author: Sheroz Khaydarov <sheroz@wordapp.io>
 * Date: 20/03/2017 Time: 08:29
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

function wa_pdx_send_response ($msg, $success = false)
{
    $response['success'] = $success;
    if ($success)
        $response['data'] = $msg;
    else
        $response['error'] = $msg;

    wp_send_json($response);
}

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

function wa_pdx_get_posts()
{
    $args = array(
        'posts_per_page' => -1,
        'orderby' => array('type','ID'),
        'post_type' => array('post','page'),
        'post_status' => 'publish,pending,draft,private,trash'
    );
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
