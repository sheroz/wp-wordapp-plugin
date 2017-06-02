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
 * Integration with SEO plugins.
 *
 * @param int $post_id Post id.
 * @param string $title Meta title.
 * @param string $description Meta description.
 * @param string $focus_keyword Focus keyword.
 *
 * @return void
 */
function wa_pdx_seo_plugins_integrate ($post_id, $title, $description, $focus_keyword)
{
    // Integration with Yoast SEO
    // more about: http://www.wpallimport.com/documentation/plugins-themes/yoast-wordpress-seo/
    if (function_exists('wpseo_set_value')) {
        if (!is_null($title))
            update_post_meta($post_id, '_yoast_wpseo_title', $title);
        if (!is_null($description))
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $description);
        if (!is_null($focus_keyword))
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);

        //    if (!is_null($post_url))
        //        update_post_meta( $post_id, '_yoast_wpseo_canonical', $post_url );
    }

    // Integration with All in One SEO Pack
    if (function_exists('aiosp_meta')) {
        if (!is_null($title))
            update_post_meta($post_id, '_aioseop_title', $title);
        if (!is_null($description))
            update_post_meta($post_id, '_aioseop_description', $description);
        if (!is_null($focus_keyword))
            update_post_meta($post_id, '_aioseop_keywords', $focus_keyword);
    }
}
