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

/**
 * Update the domain specific scripts
 * 
 * @since       1.4.1
 * 
 * @return void
 */

function wa_pdx_update_scripts($params)
{
    if (!empty($params))
    {
        $tracking_scripts = $params['tracking_scripts'];
        $additional_scripts = $params['additional_scripts'];
        $amp_body_scripts = $params['amp_body_scripts'];
        if (empty($tracking_scripts))
        {
            $tracking_scripts = '';
        }
        if (empty($additional_scripts))
        {
            $additional_scripts = '';
        }
        if (empty($amp_body_scripts))
        {
            $amp_body_scripts = '';
        }
        $tracking_scripts = stripslashes($tracking_scripts);
        $additional_scripts = stripslashes($additional_scripts);
        $amp_body_scripts = stripslashes($amp_body_scripts);
        update_option( PDX_CONFIG_TRACKING_SCRIPTS_KEY, $tracking_scripts );
        update_option( PDX_CONFIG_ADDITIONAL_SCRIPTS_KEY, $additional_scripts );
        update_option( PDX_CONFIG_AMP_BODY_SCRIPTS_KEY, $amp_body_scripts );
        if (PDX_LOG_ENABLE)
        {
            $log= "\nwa_pdx_update_scripts(): Stored tracking scripts:\n*** BEGIN ***\n".$tracking_scripts."\n*** END ***\n";
            $log= "\nwa_pdx_update_scripts(): Stored additional scripts:\n*** BEGIN ***\n".$additional_scripts."\n*** END ***\n";
            $log= "\nwa_pdx_update_scripts(): Stored amp body scripts:\n*** BEGIN ***\n".$amp_body_scripts."\n*** END ***\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
    }
    else{
        wa_pdx_send_response('script_params_empty');
    }
    wa_pdx_send_response('scripts_send_successfully', true);
}

/**
 * Injects scripts into head section
 * 
 * @since       1.4.1
 * 
 * @return void
 */
function wa_pdx_hook_head()
{
    // process the meta 
    if ( is_page() || is_single() ) {
        if (!wa_pdx_is_any_seo_plugin_exists())
        {
            $post_id = get_queried_object_id();
            $meta_description = get_post_meta($post_id, '_wa_pdx_meta_desc', true);
            if (!empty($meta_description))
            {
                echo '<meta name=”description” content=”' . $meta_description . '” />';
            }    
        }
    }

    // print the domain related scripts
    $tracking_scripts = get_option( PDX_CONFIG_TRACKING_SCRIPTS_KEY, '');
    $additional_scripts = get_option( PDX_CONFIG_ADDITIONAL_SCRIPTS_KEY, '');
    if (!empty($tracking_scripts))
    {
        echo $tracking_scripts;
    }
    if (!empty($additional_scripts))
    {
        echo $additional_scripts;
    }
    if (PDX_LOG_ENABLE)
    {
        $log= "\nwa_pdx_hook_head(): Loaded tracking scripts:\n*** BEGIN ***\n".$tracking_scripts."\n*** END ***\n";
        $log= "\nwa_pdx_hook_head(): Loaded additional scripts:\n*** BEGIN ***\n".$additional_scripts."\n*** END ***\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
}

/**
 * Puts title into head section
 * 
 * @since       1.4.1
 * 
 * @return void
 */
function wa_pdx_hook_title($post_title, $post_id)
{
    if ( is_page() || is_single() ) {
        if (!wa_pdx_is_any_seo_plugin_exists())
        {
            $meta_title = get_post_meta($post_id, '_wa_pdx_meta_title', true);
            if (!empty($meta_title))
            {
                $post_title = $meta_title;
            }
        }
    }
    if (PDX_LOG_ENABLE)
    {
        $log = "\nwa_pdx_hook_title(): \n";
        $log .= "post_id: ".print_r($post_id, true)."\n";
        $log .= "post_title: ".print_r($post_title, true)."\n";
        $log .= "\n---\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
    return $post_title;
}

/**
 * Checks if any SEO plugin is exists
 * 
 * @since       1.4.1
 * 
 * @return boolean
 */
function wa_pdx_is_any_seo_plugin_exists()
{
    $seo_plugin_exists = class_exists('WPSEO_Meta') || function_exists('wpseo_set_value') || function_exists('aiosp_meta') ||  class_exists('SEO_Ultimate');
    if (PDX_LOG_ENABLE)
    {
        $log= "\nwa_pdx_is_any_seo_plugin_exists(): ".var_export($seo_plugin_exists, true)."\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
    return $seo_plugin_exists;
}

/**
 * Injects scripts into body section for amp sites
 * 
 * @since       1.4.2
 * 
 * @return void
 */
function wa_pdx_hook_body()
{
    $amp_body_scripts = get_option( PDX_CONFIG_AMP_BODY_SCRIPTS_KEY, '');
    if (!empty($amp_body_scripts))
    {
        echo $amp_body_scripts;
    }
    if (PDX_LOG_ENABLE)
    {
        $log= "\wa_pdx_hook_body(): Loaded amp body tracking scripts:\n*** BEGIN ***\n".$amp_body_scripts."\n*** END ***\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
}
