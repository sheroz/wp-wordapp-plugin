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
 * Supported plugins: Yoast SEO, All in One SEO Pack
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

function wa_pdx_get_slimstat_token ()
{
    if (class_exists('wp_slimstat')) {
        $slimstat_options = get_option('slimstat_options');
        $rest_api_tokens = $slimstat_options['rest_api_tokens'];
        wa_pdx_send_response($rest_api_tokens, true);
    }
}