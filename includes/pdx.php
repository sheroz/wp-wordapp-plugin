<?php
/**
 * Author: Sheroz Khaydarov <sheroz@wordapp.io>
 * Date: 20/03/2017 08:29
 */

require plugin_dir_path( __FILE__ ) . 'common.php';
require plugin_dir_path( __FILE__ ) . 'config.php';
require plugin_dir_path( __FILE__ ) . 'constants.php';
require plugin_dir_path( __FILE__ ) . 'content.php';
require plugin_dir_path( __FILE__ ) . 'hello.php';
require plugin_dir_path( __FILE__ ) . 'integrations.php';
require plugin_dir_path( __FILE__ ) . 'media.php';
require plugin_dir_path( __FILE__ ) . 'meta.php';
require plugin_dir_path( __FILE__ ) . 'preview.php';

function ajax_wa_pdx() {

    $date = new DateTime('NOW');
    $log = '';

    if (PDX_LOG_ENABLE)
        $log.= "\n\najax_wordapp_seo(): started at ".$date->format('Y-m-d H:i:s') . "\n";

//    $log.= "---- begin of headers ----\n";
//    foreach (getallheaders() as $name => $value) {
//        $log.= "$name: $value\n";
//    }
//    $log.= "---- end of headers ----\n";

//    if(!empty($_GET['token']))
//        $log.= "token: " . $_GET['token'] . "\n";

//    if(!empty($_GET['check-wa-pdx']))
//        wa_pdx_send_response(PDX_PLUGIN_VERSION_NUMBER, true);

    $cfg = get_option( PDX_CONFIG_OPTION_KEY );

    if (PDX_LOG_ENABLE)
    {
        $log.= "config set: " . json_encode($cfg) ."\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

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
        {
            $log.= "post data: $data\n";
            $log.= "post data by print_r()\n--- Begin print_r():\n".print_r($data, true)."\n--- End print_r():\n";
            $log.= "received json after json_encode: ".json_encode($json)."\n";
        }
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
            if ( $op == PDX_OP_CONFIG_SET ) {
                wa_pdx_op_config_set($json['data']);
            }
            else if ( $op == PDX_OP_CONFIG_CHECK ) {
                if (empty($cfg))
                    wa_pdx_send_response('Not Configured', false);
                else
                    wa_pdx_send_response($cfg->pdx_api_version, true);
            }

            if (!$is_authorized)
                wa_pdx_send_response('Not authorized');

            switch ($op) {

                case PDX_OP_CONTENT_GET_LIST:
                    wa_pdx_op_content_get_list();
                    break;

                case PDX_OP_CONTENT_ADD:
                    wa_pdx_op_content_add($json['data']);
                    break;

                case PDX_OP_CONTENT_UPDATE:
                    wa_pdx_op_content_update($json['data']);
                    break;

                case PDX_OP_CONTENT_GET:
                    wa_pdx_op_content_get($json['data']);
                    break;

                case PDX_OP_MEDIA_GET_LIST:
                    wa_pdx_op_media_get_list();
                    break;

                case PDX_OP_MEDIA_ADD:
                    wa_pdx_op_media_add($json['data']);
                    break;

                case PDX_OP_MEDIA_ADD_FROM_URL:
                    wa_pdx_op_media_add_from_url($json['data']);
                    break;

                case PDX_OP_PREPARE_PREVIEW:
                    wa_pdx_op_prepare_preview($json['data']);
                    break;

                case PDX_OP_META_GET:
                    wa_pdx_op_meta_get($json['data']);
                    break;

                case PDX_OP_META_UPDATE:
                    wa_pdx_op_meta_update($json['data']);
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