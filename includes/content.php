<?php
/**
 * Author: Sheroz Khaydarov <sheroz@wordapp.io>
 * Date: 20/03/2017 Time: 08:29
 */

function wa_pdx_op_content_get_list ()
{
    $cfg = get_option(PDX_CONFIG_OPTION_KEY);
    if (empty($cfg))
        wa_pdx_send_response('Invalid Configuration');

    $preview_token = $cfg['preview_token'];
    $posts = wa_pdx_get_posts();
    $count = count($posts);
    for ($pos = 0; $pos < $count; $pos++)
    {
        $wa_pat = wa_pdx_generate_preview_access_token ($posts[$pos]['ID'], $preview_token);
        if ($posts[$pos]['status'] != 'publish')
            $posts[$pos]['preview_url'] = wa_pdx_add_url_params ($posts[$pos]['url'], "preview=true&wa_pat=$wa_pat");
        else
            $posts[$pos]['preview_url'] = wa_pdx_add_url_params ($posts[$pos]['url'], "wa_pat=$wa_pat");
    }
    wa_pdx_send_response($posts, true);
}

function wa_pdx_op_content_add ($params)
{
    wa_pdx_content_add($params);
    wa_pdx_send_response('', true);
}

function wa_pdx_content_add ($params)
{
    $post_content = $params['content'];

    $post = array(
        'post_content' => $post_content
    );

    $post_title  = $params['title'];
    if (!is_null($post_title))
        $post['post_title'] = $post_title;

    $post_type = $params['type'];
    if (!is_null($post_type))
        $post_type = "page";
    $post['post_type'] = $post_type;

    $post_status = $params['status'];
    if (!is_null($post_status))
        $post_status = "draft";
    $post['post_status'] = $post_status;

    $post_meta_description = $params['description'];
    if (!is_null($post_meta_description))
        $post['post_excerpt'] = $post_meta_description;

    $post_id = wp_insert_post($post);
    $success = $post_id!=0;

    $focus_keyword = $params['focus_keyword'];
    wa_pdx_seo_plugins_integrate ($post_id, $post_title, $post_meta_description, $focus_keyword);

    if (!$success)
        return null;

    return $post_id;

}

function wa_pdx_content_update ($params)
{
    $post_id = $params['id'];
    $post_url = $params['url'];
    $post_content = $params['content'];

    if (empty($post_id))
    {
        if(empty($post_url))
            wa_pdx_send_response('Empty post url');

        $post_id = wa_pdx_find_post_by_url ($post_url);
        if (empty($post_id))
            wa_pdx_send_response('Cannot find post by url: ' . $post_url);
    }

    $look_for_markers = true;
    if ($look_for_markers) {
        $post = get_post($post_id, ARRAY_A);
        if (!is_null($post))
        {
            $content = $post['post_content'];
            if (!empty($content))
            {
                $marker_pos_start = strpos ($content, PDX_MARKER_CONTENT_BEGIN , 0);
                if ($marker_pos_start !== false) {
                    $h1_replace = true;
                    if ($h1_replace) {
                        $h1_content_start = strpos ($content, '<h1' , 0);
                        if ($h1_content_start === false)
                            $h1_content_start = strpos ($content, '<H1' , 0);
                        if ($h1_content_start !== false) {
                            // look for h1 tag closer '>'
                            $h1_content_start = strpos($content, '>', $h1_content_start);
                            if ($h1_content_start !== false) {
                                $h1_content_start += 1;
                                $h1_content_end = strpos($content, '<', $h1_content_start);
                                if ($h1_content_end !== false) {
                                    $h1_content_len = $h1_content_end - $h1_content_start;

                                    $h1_wa_start_outer = strpos($post_content, '<h1', 0);
                                    if ($h1_wa_start_outer === false)
                                        $h1_wa_start = strpos($post_content, '<H1', 0);
                                    if ($h1_wa_start_outer !== false) {
                                        // look for h1 tag closer '>'
                                        $h1_wa_start_inner = strpos($post_content, '>', $h1_wa_start_outer);
                                        if ($h1_wa_start_inner !== false) {
                                            $h1_wa_start_inner += 1;
                                            $h1_wa_end_inner = strpos($post_content, '<', $h1_wa_start_inner);
                                            if ($h1_wa_end_inner !== false) {
                                                $h1_wa_end_outer = strpos($post_content, '>', $h1_wa_end_inner);
                                                $h1_wa_len_outer = $h1_wa_end_outer - $h1_wa_start_outer + 1;
                                                $h1_wa_len_inner = $h1_wa_end_inner - $h1_wa_start_inner;
                                                $h1_wa = substr($post_content, $h1_wa_start_inner, $h1_wa_len_inner);
                                                $post_content = substr_replace($post_content, '', $h1_wa_start_outer, $h1_wa_len_outer);
                                                $content = substr_replace($content, $h1_wa, $h1_content_start, $h1_content_len);
                                            }
                                        }
                                    }
                                }
                            }

                        }
                    }

                    $marker_pos_start = strpos ($content, PDX_MARKER_CONTENT_BEGIN , 0);
                    $marker_pos_end = strpos ($content, PDX_MARKER_CONTENT_END , $marker_pos_start);
                    if ($marker_pos_end !== false) {
                        $marker_content_start = $marker_pos_start + strlen(PDX_MARKER_CONTENT_BEGIN);
                        $marker_content_len = $marker_pos_end - $marker_content_start;
                        $post_content = substr_replace($content, $post_content, $marker_content_start, $marker_content_len);
                    }

                }
            }

        }
    }

    $post = array(
        'ID'           => $post_id,
        'post_content' => $post_content
    );

    $post_title  = $params['title'];
    if (!is_null($post_title))
        $post['post_title'] = $post_title;

    $post_type = $params['type'];
    if (!is_null($post_type))
        $post['post_type'] = $post_type;

    $post_status = $params['status'];
    if (!is_null($post_status))
        $post['post_status'] = $post_status;

    $post_meta_description = $params['description'];
    if (!is_null($post_meta_description))
        $post['post_excerpt'] = $post_meta_description;

    $success = wp_update_post($post, false)!=0;

    $focus_keyword = $params['focus_keyword'];
    wa_pdx_seo_plugins_integrate ($post_id, $post_title, $post_meta_description, $focus_keyword);

    if (!$success)
        return null;

    return $post_id;
}

function wa_pdx_op_content_update ($params)
{
    $post_id = wa_pdx_content_update ($params);
    wa_pdx_send_response('', $post_id != null);
}

function wa_pdx_op_content_get ($params)
{
    if (!empty($params))
    {
        $content_id = $params['content_id'];
        if (!empty($content_id) && $content_id > 0)
        {
            $post = get_post( $content_id, ARRAY_A);
            if($post)
            {
                $post['url'] = get_permalink( $post['ID'] );
                wa_pdx_send_response($post, true);
            } else
                wa_pdx_send_response('Content not found');
        } else
            wa_pdx_send_response('Invalid content id');
    } else
        wa_pdx_send_response('Empty data parameter');
}
