<?php
/**
 * User: sheroz
 * Date: 20/03/2017
 * Time: 08:29
 */

require_once 'wa-api-pdx-const.php';

function wa_pdx_generate_preview_access_token ($post_id, $preview_token)
{
    // pat means: Preview Access Token
    // pat should be random for every function call to avoid browser cache
    // and must be verifiable by preview_token

    // !!! temporary solution. Needs to improve for security reasons
    // !!! ex. we can  pat = ($salt XOR $preview_token) + $salt
    // !!! OR use hash with post related stuff to prevent predictions
    $salt = wa_pdx_random_hex_string(16);
    $wa_pat = $preview_token . $salt;

    return $wa_pat;
}

function wa_pdx_check_preview_access_token ($post_id, $wa_pat, $preview_token)
{
    return substr($wa_pat, 0, strlen($preview_token)) === $preview_token;
}

function wa_pdx_add_url_params ($url, $params)
{
    if (empty($params))
        return $url;

    $query = '?';
    if (strpos($url, $query) !== false)
        $query = '&';

    if (is_array($params))
    {
        $prefix = '';
        foreach ($params as $param)
        {
            $query .= $prefix . $param;
            $prefix = '&';
        }
    }
    else
        $query .= $params;

    return $url . $query;
}

function wa_pdx_random_hex_string($len)
{
    $chars = '0123456789abcdef';
    $chars_len = strlen($chars);
    $res = '';
    for ($pos = 0; $pos < $len; $pos++)
        $res .= $chars[rand(0, $chars_len - 1)];
    return $res;
}

function wa_pdx_filter_pre_get_posts( $query )
{
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

function wa_pdx_hello()
{
    if($_SERVER['REQUEST_METHOD'] === 'HEAD')
    {
        if (!empty($_SERVER['HTTP_X_WA_PDX_HELLO']))
        {
            $admin_ajax_url = admin_url('admin-ajax.php');
            header('X-WA-PDX-VERSION: ' . PDX_PLUGIN_VERSION_NUMBER);
            header('X-WA-PDX-AJAX-URL: ' . $admin_ajax_url);
            if (PDX_LOG_ENABLE)
            {
                $log  = "Catched Wordapp Hello Message:\nX-WA-PDX-HELLO: ".$_SERVER['HTTP_X_WA_PDX_HELLO']."\n";
                $log .= "Response headers added:\n";
                $log .= 'X-WA-PDX-VERSION: ' . PDX_PLUGIN_VERSION_NUMBER . "\n";
                $log .= 'X-WA-PDX-AJAX-URL: ' . $admin_ajax_url . "\n";
                file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
            }
        }
    }
}

function wa_pdx_clear_config()
{
    delete_option( PDX_CONFIG_OPTION_KEY );
    if (PDX_LOG_ENABLE)
    {
        $log = "Plugin Configuration Removed.\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
}

function wa_pdx_send_response ($data, $success = false)
{
    $response['success'] = $success;
    $response['data'] = $data;
    wp_send_json($response);
}

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

    $is_authorized = ( !empty($cfg_token) && ($cfg_token == $validation_token));
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
            $log.= "received json: ".json_encode($json)."\n";
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
            if ( $op == PDX_OP_CONFIG_SET )
            {
                if (PDX_LOG_ENABLE)
                    $log = "--- PDX_OP_CONFIG_SET ---\n";

                $params = $json['data'];
                if (empty($params))
                    wa_pdx_send_response('Invalid Data');


                // !!! begin: remove this block !!! this is for demo use only
                $pdx_config = $params;
                // save configuration into option table
                update_option( PDX_CONFIG_OPTION_KEY, $pdx_config );
                if (PDX_LOG_ENABLE)
                {
                    $log.= "direct config set: " . json_encode($pdx_config) ."\n";
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }
                wa_pdx_send_response('Configuration set successfully', true);
                // !!!! end: remove this block !!! this is for demo use only


                $ticket_id = $params['ticket_id'];
                $api_pdx_url = $params['api_pdx_url'];
                $timestamp = $params['timestamp'];
                $signature = $params['signature'];

                if (empty($ticket_id) || empty($api_pdx_url))
                    wa_pdx_send_response('Invalid Params');

/*                // test signature
                $signed_data = '02cb861f54267b72e01b5f522a904b20ff9bd604d94a039b90d6a8ef1e10006a8ehttp://localhost:3000/api/pdx';
                $signature_hex_ruby = '8a9e38a3aba180f8424b20e7fe3e3075569032e30ff0fdef29031f0701ebda853b26d8d8bc5f698151780a287072fb78811fb3259f4e2b38e3e5cde82a92f3e58deaf378182821d7b03b0ce7682089eac0b56bb48a02b6a9281154e524056b8b3ccbb6666aca1d5271046dd997e0e627080f757af1db7b80238e7747dcdacd7e2db3409998b3c99474ad02ed5f82ef8118ff5a668604a02a758577fa1d66fdf96afc719a10ca015e3c5327a978c7275f0f88f1c8e84b90cb46dab592abbaa03fdc49dfd703f489124ba43f1283dd939b3ea9dda75c9d68c548ac8e1ad1416c934d6db5563769578c0c8ddc1d9a4fe40cebcb007b5f525a9e0a57faa820234be0';
                $signature_php ='';

                openssl_sign($signed_data, $signature_php, $PDX_PRV_KEY_PEM_2048, "SHA256");

                $signature_php_hex = bin2hex($signature_php);
                $log.= "RUBY signature: " . $signature_hex_ruby ."\n";
                $log.= "PHP signature : " . $signature_php_hex ."\n";

                //    function hex2bin($data) {
                //        $len = strlen($data);
                //        return pack("H" . $len, $data);
                //    }

                $ok = openssl_verify($signed_data, $signature_php, $PDX_PUB_KEY_PEM_2048, "SHA256");
              if ($ok == 1) {
                    $log.= "PHP signature good\n";
                } elseif ($ok == 0) {
                    $log.= "PHP signature bad\n";
                } else {
                    $log.= "PHP signature ugly, error checking signature\n";
                }

*/
                if (PDX_SIGNATURE_CHECK == 1)
                {
                    $reassembled_data = sprintf("%02x", $op);
                    $reassembled_data .= $ticket_id;
                    $reassembled_data .= $api_pdx_url;
                    $reassembled_data .= dechex($timestamp);

                    $reassembled_data = strtolower ( $reassembled_data);

                    $signature = pack("H" . strlen($signature), $signature);
                    $verified = openssl_verify($reassembled_data, $signature, PDX_PUB_KEY_PEM_2048, "SHA256");

                    if (PDX_LOG_ENABLE)
                    {
                        $log.= "Reassembled data: " . $reassembled_data ."\n";
                        if ($verified == 1) {
                            $log.= "Signature verification OK\n";
                        } elseif ($verified == 0) {
                            $log.= "Signature verification FAILED\n";
                        } else {
                            $log.= "Signature verification ugly, error checking signature\n";
                        }
                        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                    }

                    if ( $verified != 1 ) {
                        wa_pdx_send_response('Signature Verification Failed');
                    }
                }

                $api_pdx_tickets_url = $api_pdx_url . '/tickets';

                if (PDX_LOG_ENABLE)
                {
                    $log = "Configuration process started...\n";
                    $log.= "Knocking the door of the Wordapp Platform with ticket: $ticket_id at $api_pdx_tickets_url\n";
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }
/*
                $data_string = json_encode(array( 'ticket_id' => $ticket_id ));
                $api_pdx_headers = array(
                    'Accept: application/json',
                    'Accept-Encoding: gzip, deflate',
                    'Accept-Language: en-US,en;q=0.8',
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: '. strlen($data_string),
                    'User-Agent: '.PDX_PLUGIN_VERSION,
                    'X-Api-Version: application/vnd.wordapp-v1+json'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$api_pdx_tickets_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $api_pdx_headers);
//                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
//                curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds

                $response = curl_exec($ch);
                curl_close($ch);
*/

                $args = array(
                    'method' => 'POST',
                    'httpversion' => '1.0',
                    'headers' => array(
                        'Accept' => 'application/json',
                        'Accept-Encoding' => 'gzip, deflate',
                        'Accept-Language' => 'en-US,en;q=0.8',
                        'Content-Type' => 'application/json; charset=utf-8',
                        'User-Agent' => PDX_PLUGIN_VERSION_TEXT,
                        'X-Api-Version' => 'application/vnd.wordapp-v1+json'
                    ),
                    'timeout' => 5,
                    'blocking' => true,
                    'body' => json_encode(array( 'ticket_id' => $ticket_id ))
                );

//                $http = new WP_Http();
//                $response = $http->request($api_pdx_tickets_url, $args);

                $response = wp_remote_post($api_pdx_tickets_url, $args);

                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    if (PDX_LOG_ENABLE)
                    {
                        $log = "Configuration fetch phase 2 failed. Url: $api_pdx_tickets_url\nError message: $error_message\n";
                        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                    }
                    wa_pdx_send_response('Configuration fetch phase 2 failed');
                }

                $response_code = wp_remote_retrieve_response_code( $response );
                if ($response_code != 200) {
                    if (PDX_LOG_ENABLE)
                    {
                        $log = "Invalid response code: $response_code\n";
                        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                    }
                    wa_pdx_send_response('Invalid response code');
                }

                $response_body = wp_remote_retrieve_body( $response );
                if (empty($response_body)) {
                    if (PDX_LOG_ENABLE)
                    {
                        $log = "Empty configuration data\n";
                        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                    }
                    wa_pdx_send_response('Empty configuration data');
                }

                if (PDX_LOG_ENABLE)
                {
                    $log = "Configuration data received: " . $response_body ."\n";
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }

                $json_config = json_decode( $response_body );

                // validate signature !!!
                $pdx_config = array (
                    'pdx_api_version'=> $json_config->pdx_api_version,
                    'validation_token' => $json_config->validation_token,
                    'preview_token' => $json_config->preview_token,
                    'timestamp' => $json_config->timestamp
                );

                if (PDX_LOG_ENABLE)
                {
                    $log = "Configuration: validation_token = " . $json_config->validation_token ."\n";
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }

                // save configuration into option table
                update_option( PDX_CONFIG_OPTION_KEY, $pdx_config );

                if (PDX_LOG_ENABLE)
                {
                    $log = "Configuration set successfully\n";
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }

                if (PDX_LOG_ENABLE)
                {
                    $cfg = get_option( PDX_CONFIG_OPTION_KEY );
                    $log = "Received: validation_token = " . $cfg['validation_token']. "\n";
                    file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
                }

                wa_pdx_send_response('Configuration set successfully', true);
            }

            // check if security token and config params are set
            if ( $op == PDX_OP_CONFIG_CHECK )
            {
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

                case PDX_OP_PREPARE_PREVIEW:
                    wa_pdx_op_prepare_preview($json['data']);
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

function wa_pdx_get_posts()
{
    $args = array(
        'posts_per_page' => -1,
        'orderby' => array('type','ID'),
        'post_type' => array('post','page'),
        'post_status' => 'publish,pending,draft,private,trash'
    );
//  'post_status' => 'publish,pending,draft,auto-draft,future,private,inherit,trash'

    $data = array ();

    $posts = get_posts($args);
    foreach ( $posts as $post ) {
        $data[] = array(
            'id'        => $post->ID,
            'url'       => get_permalink( $post->ID ),
            'type'      => $post->post_type,
            'title'     => $post->post_title,
            'status'    => get_post_status( $post->ID )
        );
    }
    wp_reset_postdata();

    return $data;
}

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
    $post = array(
        'post_type'     => $params['type'],
        'post_status'   => $params['status'],
        'post_title'    => $params['title'],
        'post_content'  => $params['content']
    );

    wp_insert_post( $post );
    wa_pdx_send_response('', true);
}

function wa_pdx_find_post_by_url ($post_url)
{
    if(empty($post_url))
        return null;

    $posts = wa_pdx_get_posts();
    foreach ($posts as $post)
        if ($post['url'] == $post_url)
            return $post['id'];

    return null;
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

    // integrate with Yoast SEO
    // more about: http://www.wpallimport.com/documentation/plugins-themes/yoast-wordpress-seo/
    if( function_exists( 'wpseo_set_value' ) ) {

        if (!is_null($post_title))
            update_post_meta( $post_id, '_yoast_wpseo_title', $post_title );
        if (!is_null($post_meta_description))
            update_post_meta( $post_id, '_yoast_wpseo_metadesc', $post_meta_description );

        $focus_keyword = $params['focus_keyword'];
        if (!is_null($focus_keyword))
            update_post_meta( $post_id, '_yoast_wpseo_focuskw', $focus_keyword );

    //    if (!is_null($post_url))
    //        update_post_meta( $post_id, '_yoast_wpseo_canonical', $post_url );

    }

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

function wa_pdx_op_media_add ($params)
{

    $jsonFile = $params['file'];
    $alt = $params['alt'];
    $caption = $params['caption'];

    $filename = $jsonFile['name'];
    $bits = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $jsonFile['body']));

    $parent_post_id = null;

    $upload_file = wp_upload_bits($filename, null, $bits);
    if (!$upload_file['error']) {

        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_excerpt' => $caption,
            'post_mime_type' => $wp_filetype['type'],
            'post_parent' => $parent_post_id,
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
        if (!is_wp_error($attachment_id)) {
//                      require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
            wp_update_attachment_metadata( $attachment_id,  $attachment_data );

            if (add_post_meta($attachment_id, '_wp_attachment_image_alt', $alt, true)){
                update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
            }

            wa_pdx_send_response('', true);

        } else
            wa_pdx_send_response($data = 'File upload error! wp_insert_attachment()');
    } else
        wa_pdx_send_response('File upload error! wp_upload_bits()');
}

function wa_pdx_op_media_get_list ()
{

    $data = array ();
//                $base_url = wp_upload_dir();
//                $base_url = $base_url['baseurl'];

    $args = array( 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
    $attachments = get_posts( $args );
    if ( $attachments ) {
        foreach ( $attachments as $attachment ) {
            $data[] =  wp_prepare_attachment_for_js( $attachment->ID );
//                        $post_id = $post->ID;
//                        $metadata = wp_get_attachment_metadata( $post->ID );
//                        if (!empty($metadata) && !empty($metadata['file']))
//                        {
//                            $file_url = $base_url .'/'. $metadata['file'];
//                            $data[] = array(
//                                'id'    => $post_id,
//                                'src'   => $post->guid,
//                                'type'  => $post->post_mime_type,
//                                'title' => $post->post_title,
//                                'status' => get_post_status( $post_id ),
//                                'metadata' => $metadata,
//                                'file_url' => $file_url,
//                                'caption' => $post->post_excerpt,
//                                'description' => $post->post_content,
//                                'href' => get_permalink( $post_id ),
//                                'alt' => get_post_meta( $post_id, '_wp_attachment_image_alt', true ),
//                                'file' => wp_prepare_attachment_for_js( $post_id )
//                            );
//                        }
        }
        wp_reset_postdata();
    }
    wa_pdx_send_response($data, true);
}

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

function wa_pdx_op_prepare_preview ($params)
{
    $preview_url = null;
    $post_id = wa_pdx_content_update ($params);
    if (empty($post_id))
        wa_pdx_send_response('Invalid Post ID');

     $preview_url = wa_pdx_generate_preview_url ($post_id);
     wa_pdx_send_response($preview_url, true);
}
