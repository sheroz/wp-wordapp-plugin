<?php

require plugin_dir_path( __FILE__ ) . 'common.php';
require plugin_dir_path( __FILE__ ) . 'config.php';
require plugin_dir_path( __FILE__ ) . 'constants.php';
require plugin_dir_path( __FILE__ ) . 'post.php';
require plugin_dir_path( __FILE__ ) . 'hello.php';
require plugin_dir_path( __FILE__ ) . 'integrations.php';
require plugin_dir_path( __FILE__ ) . 'media.php';
require plugin_dir_path( __FILE__ ) . 'meta.php';
require plugin_dir_path( __FILE__ ) . 'preview.php';
require plugin_dir_path( __FILE__ ) . 'schedule.php';
require plugin_dir_path( __FILE__ ) . 'settings.php';

/**
 * The main AJAX entry point of the Wordapp plugin.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * The main AJAX entry point of the Wordapp plugin.
 * Parses AJAX requests and calls appropriate functions.
 *
 * @internal
 *
 * @param array $_POST HTTP POST parameters passed from Wordapp Platform.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function ajax_wa_pdx() {

    $date = new DateTime('NOW');
    $log = '';

    if (PDX_LOG_ENABLE)
        $log.= "\n**********\najax_wa_pdx(): started at ".$date->format('Y-m-d H:i:s') . "\n";

    $cfg = get_option( PDX_CONFIG_OPTION_KEY );

    if (PDX_LOG_ENABLE)
        $log.= "config set: " . json_encode($cfg) ."\n";

    $validation_token = '';
    if (!empty($_POST['token']))
        $validation_token = $_POST['token'];

    $cfg_token = $cfg['validation_token'];
    if (PDX_LOG_ENABLE)
    {
        $log.= "config token: $cfg_token\n";
        $log.= "received token: $validation_token\n";

    }

    $is_authorized = ( !empty($cfg_token) && ($cfg_token === $validation_token));
    if (PDX_LOG_ENABLE)
        if ($is_authorized)
            $log.= "token verification OK.\n";
        else
            $log.= "token verification FAILED.\n";

    $json = null;
    $data = $_POST['data'];
    if (!empty($data)) {
        $json = $data;
        if (PDX_LOG_ENABLE)
            $log.= "post data by print_r()\n--- Begin print_r():\n".print_r($data, true)."\n--- End print_r():\n";
    }
    else
        if (PDX_LOG_ENABLE)
            $log.= "post data not found\n";

    if (PDX_LOG_ENABLE)
    {
        $log.= "\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

    if($json)
    {
        $op = $json['op'];

        if ($op && !empty($op))
        {
            if ( $op == PDX_OP_WP_CONFIG_SET ) {
                wa_pdx_op_config_set($json['data']);
            } elseif ( $op == PDX_OP_WP_CONFIG_CHECK ) {
                wa_pdx_op_config_check();
            }

            if (!$is_authorized)
                wa_pdx_send_response('Not authorized');

            $params = $json['data'];

            switch ($op) {

                case PDX_OP_WP_POST_LIST:
                    wa_pdx_op_post_list($params);
                    break;

                case PDX_OP_WP_POST_ADD:
                    wa_pdx_op_post_add($params);
                    break;

                case PDX_OP_WP_POST_UPDATE:
                    wa_pdx_op_post_update($params);
                    break;

                case PDX_OP_WP_POST_GET:
                    wa_pdx_op_post_get($params);
                    break;

                case PDX_OP_WP_MEDIA_LIST:
                    wa_pdx_op_media_list($params);
                    break;

                case PDX_OP_WP_MEDIA_ADD:
                    wa_pdx_op_media_add($params);
                    break;

                case PDX_OP_WP_MEDIA_ADD_FROM_URL:
                    wa_pdx_op_media_add_from_url($params);
                    break;

                case PDX_OP_WP_PREPARE_PREVIEW:
                    wa_pdx_op_prepare_preview($params);
                    break;

                case PDX_OP_WP_POST_META_GET:
                    wa_pdx_op_meta_get($params);
                    break;

                case PDX_OP_WP_POST_META_UPDATE:
                    wa_pdx_op_meta_update($params);
                    break;

                case PDX_OP_WP_POST_TYPE_LIST:
                    wa_pdx_op_post_type_list();
                    break;

                case PDX_OP_WP_POST_STATUS_LIST:
                    wa_pdx_op_post_status_list();
                    break;

                case PDX_OP_WP_POST_TEMPLATE_LIST:
                    wa_pdx_op_post_template_list();
                    break;

                case PDX_OP_WP_USER_LIST:
                    wa_pdx_op_user_list();
                    break;

                default:
                    wa_pdx_send_response('No valid command');
            }
        }
        else
            wa_pdx_send_response('No command found');
    }
    else
        wa_pdx_send_response('Invalid Data');
}


// get_sample_permalink( int $id, string $title = null, string $name = null )
// https://developer.wordpress.org/reference/functions/get_sample_permalink/

// $post_id = 45; //specify post id here
// $post = get_post($post_id);
// $slug = $post->post_name;

// preview mode
// https://www.webhostinghero.com/how-to-share-a-draft-page-in-wordpress/
// add_filter( 'posts_results', 'wa_pdx_set_query_to_draft', 0, 2 );

// https://www.smashingmagazine.com/2011/03/ten-things-every-wordpress-plugin-developer-should-know/
