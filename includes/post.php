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
 * @api
 *
 * Retrieves list of all post types.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_type_list(){
    $post_types = get_post_types();
    wa_pdx_send_response($post_types, true);
}

/**
 * @api
 *
 * Retrieves list of all post statuses.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_status_list(){
    $stati = get_post_stati();
    wa_pdx_send_response($stati, true);
}

/**
 * @api
 *
 * Retrieves list of all templates of active theme.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_template_list(){
    $templates = get_page_templates();
    wa_pdx_send_response($templates, true);
}

/**
 * @api
 *
 * Retrieves list of posts matching criteria.
 *
 * @param array $params The parameters passed from Wordapp.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_list ($params)
{
    $cfg = get_option(PDX_CONFIG_OPTION_KEY);
    if (empty($cfg))
        wa_pdx_send_response('Invalid Configuration');

    $preview_token = $cfg['preview_token'];
    $posts = wa_pdx_get_posts($params);
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

/**
 * @internal
 *
 * Updates post template.
 *
 * @param int $post_id The post id to assign template.
 * @param string $name The template name to assign.
 *
 */
function wa_pdx_post_update_template($post_id, $name) {
    if ($post_id && $post_id > 0) {
        if (!is_null($name)) {
            $template_id = null;
            $templates = get_page_templates();
            if ( $templates ) {
                foreach ( $templates as $k => $v ) {
                    if ($k == $name) {
                        $template_id = $v;
                        break;
                    }
                }
            }
            if ($template_id) {
                $post = array(
                    'ID'           => $post_id,
                    'page_template' => $template_id
                );
                wp_update_post($post, false);
                update_post_meta($post_id, '_wp_page_template', array($template_id));
            }
        }
    }
}

/**
 * @internal
 *
 * Processes post parameters.
 *
 * @param array $post The initial parameters.
 * @param array $params The parameters to parse.
 *
 * @return array The altered post parameters
 */
function wa_pdx_post_process_params ($post, $params, $add = false) {

    $post_title  = $params['title'];
    if (!is_null($post_title))
        $post['post_title'] = $post_title;

    $post_type = $params['type'];
    if (!is_null($post_type))
        $post['post_type'] = $post_type;
    else
        if($add)
            $post['post_type'] = 'page';

    $post_status = $params['status'];
    if (!is_null($post_status))
        $post['post_status'] = $post_status;
    else
        if($add)
            $post['post_status'] = 'draft';

    $publisher = $params['publisher'];
    if (!is_null($publisher))
    {
        $user = get_user_by( 'login', $publisher );
        if ($user)
            $post['post_author'] = $user->ID;
    }

    $post_meta_description = $params['description'];
    if (!is_null($post_meta_description))
        $post['post_excerpt'] = $post_meta_description;

    return $post;
}

/**
 * @internal
 *
 * Adds a new post.
 *
 * @param array $params The post parameters.
 *
 * @return int|null The post id of the added post
 */
function wa_pdx_post_add ($params)
{
    $post_content = $params['content'];

    $post = array(
        'post_content' => $post_content
    );

    $post = wa_pdx_post_process_params ($post, $params, true);

    $post_id = wp_insert_post($post);
    $success = $post_id!=0;

    if ($success)
        wa_pdx_post_update_template($post_id, $params['template']);

    $focus_keyword = $params['focus_keyword'];
    $title = $params['title'];
    $description = $params['description'];
    wa_pdx_seo_plugins_integrate ($post_id, $title, $description, $focus_keyword);

    if (!$success)
        return null;

    return $post_id;

}

/**
 * @internal
 *
 * Updates a post.
 *
 * @param array $params The post parameters.
 *
 * @return int|null The post id of the added post
 */
function wa_pdx_post_update ($params)
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

    $post = wa_pdx_post_process_params ($post, $params);
    $success = wp_update_post($post, false)!=0;

    if ($success)
        wa_pdx_post_update_template($post_id, $params['template']);


    $focus_keyword = $params['focus_keyword'];
    $title = $params['title'];
    $description = $params['description'];
    wa_pdx_seo_plugins_integrate ($post_id, $title, $description, $focus_keyword);

    if (!$success)
        return null;

    return $post_id;
}

/**
 * @api
 *
 * Adds new post.
 *
 * @param array $params The parameters passed from Wordapp.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_add ($params)
{
    $post_id = wa_pdx_post_add($params);
    if ($post_id)
    {
        $post = get_post( $post_id, ARRAY_A);
        if($post)
        {
            $post['url'] = get_permalink( $post['ID'] );
            wa_pdx_send_response($post, true);
        } else
            wa_pdx_send_response('Internal Error. Post not found');
    } else
        wa_pdx_send_response('Internal Error. Post not added');
}

/**
 * @api
 *
 * Updates post by post id or url.
 *
 * @param array $params The parameters passed from Wordapp.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_update ($params)
{
    $post_id = wa_pdx_post_update ($params);
    if ($post_id)
    {
        $post = get_post( $post_id, ARRAY_A);
        if($post)
        {
            $post['url'] = get_permalink( $post['ID'] );
            wa_pdx_send_response($post, true);
        } else
            wa_pdx_send_response('Internal Error. Post not found');
    } else
        wa_pdx_send_response('Internal Error. Post not updated');
}

/**
 * @api
 *
 * Gets a post.
 *
 * @param array $params The parameters passed from Wordapp.
 *                      The post id must be given in 'id' field.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_post_get ($params)
{
    if (!empty($params))
    {
        $post_id = $params['id'];
        if (!empty($post_id) && $post_id > 0)
        {
            $post = get_post( $post_id, ARRAY_A);
            if($post)
            {
                $post['url'] = get_permalink( $post['ID'] );
                wa_pdx_send_response($post, true);
            } else
                wa_pdx_send_response('Post not found');
        } else
            wa_pdx_send_response('Invalid post id');
    } else
        wa_pdx_send_response('Empty data parameter');
}
