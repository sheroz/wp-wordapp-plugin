<?php
/**
 * Third party integrations
 *
 * @author      Sheroz Khaydarov http://sheroz.com
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        https://github.com/sheroz/wp-wordapp-plugin
 * @since       1.0.0
 */

/**
 * Integration with SEO related plugins.
 * Supported plugins: Yoast SEO, All in One SEO Pack, SEO Ultimate
 *
 * @param int $post_id Post id.
 * @param string $title Meta title.
 * @param string $description Meta description.
 * @param string $focus_keyword Focus keyword.
 *
 * @return void
 */
function wa_pdx_seo_plugins_integrate ($post_id, $meta_title, $meta_description, $focus_keyword)
{
    // a good source for other integrations: https://github.com/pdclark/ajax-post-meta/blob/master/ajax-post-meta.php

    // Store metas for built-in renderer
    if (!is_null($meta_title))
        update_post_meta($post_id, '_wa_pdx_meta_title', $meta_title);
    if (!is_null($meta_description))
        update_post_meta($post_id, '_wa_pdx_meta_desc', $meta_description);
    if (!is_null($focus_keyword))
        update_post_meta($post_id, '_wa_pdx_meta_focuskw', $focus_keyword);

    // Integration with Yoast SEO
    // more about: http://www.wpallimport.com/documentation/plugins-themes/yoast-wordpress-seo/
    if (class_exists('WPSEO_Meta') || function_exists('wpseo_set_value')) {
        if (!is_null($meta_title))
            update_post_meta($post_id, '_yoast_wpseo_title', $meta_title);
        if (!is_null($meta_description))
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
        if (!is_null($focus_keyword))
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);

        //    if (!is_null($post_url))
        //        update_post_meta( $post_id, '_yoast_wpseo_canonical', $post_url );
    }

    // Integration with All in One SEO Pack
    if (function_exists('aiosp_meta')) {
        if (!is_null($meta_title))
            update_post_meta($post_id, '_aioseop_title', $meta_title);
        if (!is_null($meta_description))
            update_post_meta($post_id, '_aioseop_description', $meta_description);
        if (!is_null($focus_keyword))
            update_post_meta($post_id, '_aioseop_keywords', $focus_keyword);
    }

    // Integration with SEO_Ultimate
    if (class_exists('SEO_Ultimate')) {
        if (!is_null($meta_title))
            update_post_meta($post_id, '_su_title', $meta_title);
        if (!is_null($meta_description))
            update_post_meta($post_id, '_su_description', $meta_description);
        if (!is_null($focus_keyword))
            update_post_meta($post_id, '_su_keywords', $focus_keyword);
    }
}

/**
 * Integration with Slimstat Analytics plugin.
 * 
 * @api
 * @since       1.3.3
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_get_slimstat_token ()
{
    if (class_exists('wp_slimstat')) {
        $slimstat_options = get_option('slimstat_options');
        
        if (empty($slimstat_options))
            update_option( 'slimstat_options', wp_slimstat::$settings );
            
        $slimstat_options = get_option('slimstat_options');
        if (!empty($slimstat_options)) {
            $rest_api_tokens = $slimstat_options['rest_api_tokens'];
            if (!is_null($rest_api_tokens)) {
                wa_pdx_send_response($rest_api_tokens, true);
            } else 
                wa_pdx_send_response('invalid_slimstat_token');
        } else
            wa_pdx_send_response('invalid_slimstat_options');
    } else
        wa_pdx_send_response('wp_slimstat_not_found');
}

/**
 * Integration with SEO and Analytics related plugins.
 * Supported plugins: Yoast SEO, All in One SEO Pack, SEO Ultimate, Slimstat Analytics
 * 
 * @api
 * 
 * @since       1.3.4
 * 
 * @return array $plugins list of plugin names required for Wordapp Platform.
 */
function wa_pdx_look_for_plugins ()
{
    $plugins = array();

    if (class_exists('WPSEO_Meta') || function_exists('wpseo_set_value')){
        array_push($plugins, "yoast");
    }
    if (function_exists('aiosp_meta')) {
        array_push($plugins, "all_in_one_seo_pack");
    }
    if (class_exists('SEO_Ultimate')) {
        array_push($plugins, "seo_ultimate");
    }
    if (class_exists('wp_slimstat')) {
        array_push($plugins, "slimstat_analytics");
    }

    return $plugins;
}

/**
 * List of plugin names required for Wordapp Platform
 * 
 * @api
 * 
 * @since       1.3.4
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_check_plugin ()
{
    $plugins = wa_pdx_look_for_plugins();
    wa_pdx_send_response($plugins, true);
}

/**
 * Get focus keywords from Yoast SEO plugin.
 * 
 * @api
 * @since       1.3.8
 * 
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_get_post_keywords ()
{
    $get_posts = array(
        'post_type' => array('post', 'page'),
        'post_status' => array('publish'),
    );
    $posts = get_posts($get_posts);
    $keywords = array();

    foreach ($posts as &$post) {
        $metas = get_post_meta($post -> ID, '_wa_pdx_meta_focuskw');
        if (empty($metas)) {
            $metas = get_post_meta($post -> ID, '_yoast_wpseo_focuskw');
        }
        if (!empty($metas)) {
            $keywords = array_merge($keywords, $metas);
        }
    }
    wa_pdx_send_response($keywords, true);
}
