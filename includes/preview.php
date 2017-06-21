<?php
/**
 * Preview related functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Filter function for preview hook.
 * This hook function is called after the query variable object is created, but before the actual query is run.
 *
 * @param object $query The WP_Query object by reference.
 *
 * @return object Returns the passed query object.
 */
function wa_pdx_filter_pre_get_posts( $query )
{
    if (PDX_CONFIG_SCHEDULE_PUBLISH_MISSED == 1)
        wa_pdx_validate_scheduled_posts();

    if (PDX_LOG_ENABLE)
    {
        $log  = "wa_pdx_filter_pre_get_posts(): Phase 1 passed\n";
        $log  .= "is_main_query(): " . $query->is_main_query() . "\n";
        $log  .= "is_preview(): " . $query->is_preview() . "\n";
        $log  .= "is_singular(): " . ($query->is_singular() ? '1':'0') . "\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

    if ($query->is_main_query() && $query->is_preview() && $query->is_singular()) {
        add_filter( 'posts_results', 'wa_pdx_filter_posts_results', 10, 2 );
        if (PDX_LOG_ENABLE)
        {
            $log  = "wa_pdx_filter_pre_get_posts(): Phase 2 passed\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
    }

    if (PDX_LOG_ENABLE)
    {
        $log  = "wa_pdx_filter_pre_get_posts(): End\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

    return $query;
}

/**
 * Filter function for preview hook.
 * Filters the raw post results array, prior to status checks.
 *
 * @param array $posts The post results array.
 *
 * @return array The altered posts array.
 */
function wa_pdx_filter_posts_results( $posts )
{
    if (PDX_LOG_ENABLE)
    {
        $log  = "wa_pdx_filter_posts_results(): Phase 1 passed\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

    remove_filter( 'posts_results', 'wa_pdx_filter_posts_results', 10 );

    if (empty($posts))
        return $posts;

    if (sizeof($posts) != 1)
        return $posts;

    $wa_pat = $_GET['wa_pat'];
    if (empty($wa_pat))
    {
        if (PDX_LOG_ENABLE)
        {
            $log = "Invalid wa_pat parameter\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
        return $posts;
    }

    $cfg = get_option(PDX_CONFIG_OPTION_KEY);
    if (empty($cfg))
    {
        if (PDX_LOG_ENABLE)
        {
            $log  = "wa_pdx_filter_posts_results(): Empty configuration\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
        return $posts;
    }

    $preview_token = $cfg['preview_token'];
    if (empty($preview_token))
    {
        if (PDX_LOG_ENABLE)
        {
            $log  = "wa_pdx_filter_posts_results(): Invalid configuration\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
        return $posts;
    }

    $post_id = $posts[0]->ID;
    if (!wa_pdx_check_preview_access_token ($post_id, $wa_pat, $preview_token))
    {
        if (PDX_LOG_ENABLE)
        {
            $log  = "wa_pdx_filter_posts_results(): Not authorized\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
        return $posts;
    }

    $posts[0]->post_status = 'publish';

    // Disable comments and pings for this post.
    add_filter('comments_open', '__return_false');
    add_filter('pings_open', '__return_false');

    if (PDX_LOG_ENABLE)
    {
        $log  = "wa_pdx_filter_posts_results(): Process completed.\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
    return $posts;
}

/**
 * Generates preview access token.
 *
 * @param int $post_id The post id to generate preview access token.
 * @param string $preview_token The security token for preview. This token sets up by configuration process.
 *
 * @return string Generated preview access token.
 */
function wa_pdx_generate_preview_access_token ($post_id, $preview_token)
{
    // pat means: Preview Access Token
    // pat should be random for every function call to avoid browser cache
    // and must be verifiable by preview_token

    // May be needs to improve for security reasons
    // ex. we can  pat = ($salt XOR $preview_token) + $salt
    // OR use hash with post related stuff to prevent predictions
    $salt = wa_pdx_random_hex_string(16);
    $wa_pat = $preview_token . $salt;

    return $wa_pat;
}

/**
 * Checks if provided token gives access for preview.
 *
 * @param int $post_id The post id to check access for preview.
 * @param string $wa_pat The preview access token.
 * @param string $preview_token The security token for preview. This token sets up by configuration process.
 *
 * @return bool Whether preview access is allowed.
 */
function wa_pdx_check_preview_access_token ($post_id, $wa_pat, $preview_token)
{
    return substr($wa_pat, 0, strlen($preview_token)) === $preview_token;
}

/**
 * Generates preview url.
 *
 * @param int $post_id The post id to generate preview url.
 *
 * @return string Preview url.
 */
function wa_pdx_generate_preview_url ($post_id)
{
    $params = array();
    $post_status = get_post_status($post_id);
    if ($post_status !== 'publish')
        $params[] = 'preview=true';

    $cfg = get_option(PDX_CONFIG_OPTION_KEY);
    if (empty($cfg))
        wa_pdx_send_response('Invalid Configuration');

    $preview_token = $cfg['preview_token'];
    $wa_pat = wa_pdx_generate_preview_access_token ($post_id, $preview_token);
    $params[] = "wa_pat=$wa_pat";
    $post_url = get_permalink($post_id);
    return wa_pdx_add_url_params($post_url , $params);
}

/**
 * Prepares unpublished post for preview
 *
 * @api
 *
 * @param array $params The parameters passed from Wordapp.
 *
 * @return string Preview url.
 */
function wa_pdx_op_prepare_preview ($params)
{
    $preview_url = null;
    $post_url = $params['url'];

    if(empty($post_url))
        $post_id = wa_pdx_post_add ($params);
    else
        $post_id = wa_pdx_post_update ($params);

    if (empty($post_id))
        wa_pdx_send_response('Invalid Post ID');

    $preview_url = wa_pdx_generate_preview_url ($post_id);
    $post_url = get_permalink($post_id);

    $data = array (
        'url' => $post_url,
        'preview_url' => $preview_url
    );

    wa_pdx_send_response($data, true);
}
