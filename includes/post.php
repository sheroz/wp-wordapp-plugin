<?php
/**
 * Post related functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Retrieves list of posts matching by query criteria.
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
            'name'      => $post->post_name,
            'title'     => $post->post_title,
            'status'    => get_post_status( $post->ID )
        );
    }
    wp_reset_postdata();
    return $data;
}

/**
 * Finds post by url.
 *
 * @param string $post_url The url to find.
 *
 * @return int|null The post id found.
 */
function wa_pdx_find_post_by_url ($post_url)
{
    if(empty($post_url))
        return null;

    $posts = wa_pdx_get_posts(null);
    foreach ($posts as $post)
        if ($post['url'] == $post_url)
            return $post['id'];

    return null;
}

/**
 * Retrieves list of all post types.
 *
 * @api
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
 * Retrieves list of all post statuses.
 *
 * @api
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
 * Retrieves list of all templates of active theme.
 *
 * @api
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
 * Retrieves list of posts by query criteria.
 *
 * @api
 *
 * @param array $params Parameters passed from Wordapp.
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
 * Updates post template option.
 *
 * @internal
 *
 * @param int $post_id The post id to assign template.
 * @param string $name The template name to assign.
 *
 */
function wa_pdx_post_update_template($post_id, $params) {

    $template_name = null;
    if ($params['options'])
        $template_name = $params['options']['wp_template'];

    if ($post_id && $post_id > 0 && $template_name)
    {
        $template_id = null;
        $templates = get_page_templates();
        if ( $templates ) {
            foreach ( $templates as $k => $v ) {
                if ($k == $template_name) {
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

/**
 * Update post's meta related parameters.
 *
 * @since 1.2.2

 * @param int $post_id Post ID
 * @param array $params Parameters to parse.
 *
 */
function wa_pdx_post_update_metas($post_id, $params)
{
    $metas = array();
    $options =  $params['options'];
    if ($options) {
        $thumbnail_id = $options['wp_thumbnail_mid'];
        if (!is_null($thumbnail_id)) {
            $metas['_thumbnail_id'] = $thumbnail_id;
        }

    }

    if ($post_id && $post_id > 0 && !empty($metas))
    {
        foreach ( $metas as $k => $v ) {
            update_post_meta($post_id, $k, $v);
        }
    }
}

/**
 * Processes post parameters.
 *
 * @param array $params Parameters to parse.
 * @param bool $add Add default params for empty ones.
 *
 * @return array The altered post parameters
 */
function wa_pdx_post_process_params ($params, $add = false) {

    $post_title = $params['meta_title'];
    $post_content = $params['html'];
    if (empty($html_content)) {
        $post_content = '';
        $nodes = $params['nodes'];
        if (!empty($nodes)) {
            if (PDX_LOG_ENABLE)
            {
                $log= "\nNodes: Before sort:\n".print_r($nodes,true)."\n";
                file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
            }
            ksort($nodes); // sort nodes on sequence
            if (PDX_LOG_ENABLE)
            {
                $log= "\nNodes: After sort:\n".print_r($nodes,true)."\n";
                file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
            }
            foreach ( $nodes as $node ) {
                $type = $node['type'];
                $text = $node['text'];
                $html_tag = $node['html_tag'];
                if ($type == 'h1') {
                    if (empty($post_content)) {
                        $post_title = $text;
                    } else {
                        $post_content .= $html_tag;
                    }
                } else if($type != 'title' && $type != 'description') {
                    $post_content .= $html_tag;
                }
            }
        }
    }

    $post = array(
        'post_content' => $post_content
    );

    if (!is_null($post_title)) {
        $post['post_title'] = $post_title;
        // $post['post_name'] = sanitize_title($post_title);
    }

    $post_meta_description = $params['meta_description'];
    if (!is_null($post_meta_description))
        $post['post_excerpt'] = $post_meta_description;

    $options =  $params['options'];
    if ($options) {
        $post_type = $options['wp_type'];
        if (!is_null($post_type))
            $post['post_type'] = $post_type;

        $post_status = $options['wp_status'];
        if (!is_null($post_status))
            $post['post_status'] = $post_status;

        $publisher = $options['wp_author'];
        if (!is_null($publisher))
        {
            $user = get_user_by( 'login', $publisher );
            if ($user)
                $post['post_author'] = $user->ID;
        }

        $schedule = $options['wp_schedule'];
        if (!is_null($schedule)) {
            $date = DateTime::createFromFormat('Y-m-d H:i', $schedule);
            if ($date) {

                $schedule_timestamp = $date->getTimestamp();
                $post_date = date('Y-m-d H:i:s',$schedule_timestamp);
                $post_date_gmt = get_gmt_from_date($post_date);
                $post['post_date'] = $post_date;
                $post['post_date_gmt'] = $post_date_gmt;
                $post['edit_date'] = 'true';
                $post['post_status'] = 'future';

                if (PDX_LOG_ENABLE)
                {
                    $log  = 'post_date: ' . $post_date . PHP_EOL;
                    $log .= 'post_date_gmt: ' . $post_date_gmt . PHP_EOL;
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }

            } else {
                wa_pdx_send_response('Invalid date (required as Y-m-d H:i ) format : ' . $schedule);
            }
        }

        $category = $options['wp_category'];
        if (!is_null($category)) {
            $category_obj = get_category_by_slug( $category );
            if ($category_obj)
                $post['post_category'] = array( $category_obj->term_id );
        }

        $tags = $options['wp_tags'];
        if (!is_null($tags)) {
            $keys = explode(',', $tags);
            $tag_keys = array();
            foreach($keys as $key) {
                $key = trim($key);
                if (!empty($key))
                    $tag_keys[] = $key;
            }
            $post['tags_input'] = $tag_keys;
        }
/*
        $slug = $options['wp_slug'];
        if (!is_null($slug)) {
            $post_name = $slug; //  wp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent );
            $post['post_name'] = $post_name;
        }
*/

    }

    if($add) {
        if (empty($post['post_type']))
            $post['post_type'] = 'page';
        if (empty($post['post_status']))
            $post['post_status'] = 'draft';
    }

    if (PDX_LOG_ENABLE)
    {
        $log= "\nProcessed params:\n".print_r($post,true)."\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

    return $post;
}

/**
 * Adds a new post.
 *
 * @param array $params The post parameters.
 *
 * @return int|null The post id of the added post
 */
function wa_pdx_post_add ($params)
{
    $post = wa_pdx_post_process_params ($params, true);
    $post_id = wp_insert_post($post);
    if ($post_id != 0) {
        wa_pdx_post_update_template($post_id, $params);
        wa_pdx_post_update_metas($post_id, $params);

        $focus_keyword = $params['focus_keyword'];
        $meta_title = $params['meta_title'];
        $meta_description = $params['meta_description'];
        wa_pdx_seo_plugins_integrate ($post_id, $meta_title, $meta_description, $focus_keyword);
        return $post_id;
    }
    return null;
}

/**
 * Updates a post by post id or url.
 *
 * @param array $params The post parameters.
 *
 * @return int|null The post id of the updated post
 */
function wa_pdx_post_update ($params)
{
    $post_id = $params['id'];
    $post_url = $params['url'];

    if (empty($post_id))
    {
        if(empty($post_url))
            wa_pdx_send_response('Empty post url');

        $post_id = wa_pdx_find_post_by_url ($post_url);
        if (empty($post_id))
            wa_pdx_send_response('Cannot find post by url: ' . $post_url);
    }

    $post = wa_pdx_post_process_params ($params);
    $post_content = $post['post_content'];

    $look_for_markers = false;
    if ($look_for_markers) {
        $post_old = get_post($post_id, ARRAY_A);
        if (!is_null($post_old))
        {
            $content = $post_old['post_content'];
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

    $post['ID'] = $post_id;
    $post['post_content'] = $post_content;

    if (wp_update_post($post, false) != 0) {
        wa_pdx_post_update_template($post_id, $params);
        wa_pdx_post_update_metas($post_id, $params);

        $focus_keyword = $params['focus_keyword'];
        $meta_title = $params['meta_title'];
        $meta_description = $params['meta_description'];
        wa_pdx_seo_plugins_integrate ($post_id, $meta_title, $meta_description, $focus_keyword);
        return $post_id;
    }
    return null;
}

/**
 * Adds new post.
 *
 * @api
 *
 * @param array $params Parameters passed from Wordapp.
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
 * Updates post by post id or url.
 *
 * @api
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
 * Gets a post by id.
 *
 * @api
 *
 * @param array $params Parameters passed from Wordapp.
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

                // rendering workaround for Visual Composer Plugin
                if ( class_exists( 'WPBMap' ) && method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
                    WPBMap::addAllMappedShortcodes();
                }

                $post['content_html'] = do_shortcode( $post['post_content'] );

                wa_pdx_send_response($post, true);
            } else
                wa_pdx_send_response('Post not found');
        } else
            wa_pdx_send_response('Invalid post id');
    } else
        wa_pdx_send_response('Empty data parameter');
}
