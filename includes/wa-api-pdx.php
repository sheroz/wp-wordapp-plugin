<?php
/**
 * User: sheroz
 * Date: 20/03/2017
 * Time: 08:29
 */

require_once 'wa-api-pdx-const.php';

function ajax_wa_pdx() {
    $PDX_PRV_KEY_PEM_2048  = "-----BEGIN RSA PRIVATE KEY-----\nMIIEpAIBAAKCAQEApMxOSkfyRShmTseKyRbtoi1qSLXPvRjr31cdNf4LFfxGAnpU\nui+ggaMkg5quMHkSflKleOFG2heWtbnXrTEKpdcvW/SFjGSsoFIm90sHG0onuPNh\nC4cP4SSLuczsg3TDIpsl+XJJnXFFM+gHiEa8gRh/BXZEG5I4pUIN3xHkl34vFgyF\nA6WphQjRx+FhPPakEozf3dShAWaFS26FKBbJXRW4cUBtvtbpPIh/MTU8Mna4h/cS\nzp/3AJB2WLvytuT2jA01DYUVjc3d8tYR+XAtJZ00+k75fM/lINrcJMrlfebb27hP\nFpoB/1YTdsUa7tpAnLTXDPBkqD2kXb6F2963YwIDAQABAoIBAFZOMQox161kVQAY\n/JQHj/gJNMpTfTIZJR5YLxIhs4iWD8woaMsBOlvqJqtNjMASCB9kBQjjYgnBpMoT\nQ+KN9neX1FOiIXa/GrDzlTiZcGVYVqlDvKUe3LAaRZrOuWa29aLgAek7c1YjBg0D\nDT7PXNV3EL65iz2tPEE/8KfMZRz4v2brxUilyrpMm1KgQlw/Lwm5Zu7+anPLyPoq\n9nHe3qazS9LCgFFv5GgRPA05jcBLJwqxqmFVNnc2LtEz59kx5wJigWGkizGz3Fmw\neHFvIESOJ+lxUHSMmQSWYbP3US+AlzmqT5mWUgjuSE8rD/OgEzt0s/uFNsI4hbFn\nuijc8YECgYEAzv9FXWxqKPE+zAHzkyGnj8a7fnEiV1tydQTWyF4cUUO5g7UAmMxt\nmHR8mijbuLx8FMOj0/jSmrrdG7le9c7y/EHxYeHqbj3Kz5i/PXGg5ZOQ2je93ZUv\nBHmblAAMlNbdGGjcQxjUhUyTtHQDz/BQWcYAZl32QWm5Meg8zw8I1WECgYEAy8+g\nnwCpX+g+CgP1jgwFlfuHQ+osOlOMDXyMyI1+LBjtjwQTLPIrz8oq3NMVAT8qTmUZ\n6MiHUvXm3TNLjm7zbFT8uTpExFrseHLn5gte6G3EAD5MeWEQdsog/z0n5JGacX1h\nQEcuZ69e4HRTtxi70wgcu6CpiT1b8lUvSG+MP0MCgYEApHPURRPUB7EaZfQK8tKj\nECwgW6VAVkz10xhEF64FK1717TiJP9vyGlQ5hjR90/gTUF/aMZcWow1gix0r33hK\nPbWaM1zL5ke7cFD3ZrZ20M37IBN3CarzTsfanauoUzudLj5o9/mrJjgfhRdCzBot\njBUtziZKdc+r7YWHgi18pCECgYBQHCxmUzxAFmlMRoIec1s+uL9SUplP08cZBfvQ\nWM1fR+0YaeGfqRDPfAedBNscwlc+uT6V4TxPv5gfGip4seO8kStFI0IkbBjGv2On\naJDFi1+fON2WWDDAgHK34LHthc3PDhlLyq7S0nvYfeDp9y7cpDOnDF+XMicR+am8\nLxxRQQKBgQDG1y3vYvb+2hx9lgWHELgCxwoAXldjyHsa7lFaG2JTNsfGl4jRPurc\nOjgHC7jJRoa3Wum9mIElvpOnQosShwlwkwvDQftLU2LDOrw++JSdXy1+eihLmVW8\nBb2z4knu3kEunoVrmhYDEZuRByRvkMRq7MslMSPmpjSDm2TvMhyYPg==\n-----END RSA PRIVATE KEY-----\n";
    $PDX_PUB_KEY_PEM_2048  = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApMxOSkfyRShmTseKyRbt\noi1qSLXPvRjr31cdNf4LFfxGAnpUui+ggaMkg5quMHkSflKleOFG2heWtbnXrTEK\npdcvW/SFjGSsoFIm90sHG0onuPNhC4cP4SSLuczsg3TDIpsl+XJJnXFFM+gHiEa8\ngRh/BXZEG5I4pUIN3xHkl34vFgyFA6WphQjRx+FhPPakEozf3dShAWaFS26FKBbJ\nXRW4cUBtvtbpPIh/MTU8Mna4h/cSzp/3AJB2WLvytuT2jA01DYUVjc3d8tYR+XAt\nJZ00+k75fM/lINrcJMrlfebb27hPFpoB/1YTdsUa7tpAnLTXDPBkqD2kXb6F2963\nYwIDAQAB\n-----END PUBLIC KEY-----\n";

    $token = 'q*ZNLR9+3s!cjfstz.@&KBY@4AerUc36';
    $log_file = '/tmp/wordapp-seo-debug.log';

    $log = "\n=====================\n";
    $log.= "ajax_wordapp_seo():\n";
    $date = new DateTime('NOW');
    $log.= $date->format('Y-m-d H:i:s');
    $log.= "\n";

    $log.= "---- begin of headers ----\n";
    foreach (getallheaders() as $name => $value) {
        $log.= "$name: $value\n";
    }
    $log.= "---- end of headers ----\n";

//    if(!empty($_GET['token']))
//        $log.= "token: " . $_GET['token'] . "\n";


    // test signature
    $signed_data = '02cb861f54267b72e01b5f522a904b20ff9bd604d94a039b90d6a8ef1e10006a8ehttp://localhost:3000/api/pdx';
    $signature_hex_ruby = '8a9e38a3aba180f8424b20e7fe3e3075569032e30ff0fdef29031f0701ebda853b26d8d8bc5f698151780a287072fb78811fb3259f4e2b38e3e5cde82a92f3e58deaf378182821d7b03b0ce7682089eac0b56bb48a02b6a9281154e524056b8b3ccbb6666aca1d5271046dd997e0e627080f757af1db7b80238e7747dcdacd7e2db3409998b3c99474ad02ed5f82ef8118ff5a668604a02a758577fa1d66fdf96afc719a10ca015e3c5327a978c7275f0f88f1c8e84b90cb46dab592abbaa03fdc49dfd703f489124ba43f1283dd939b3ea9dda75c9d68c548ac8e1ad1416c934d6db5563769578c0c8ddc1d9a4fe40cebcb007b5f525a9e0a57faa820234be0';
    $signature_php ='';

    openssl_sign($signed_data, $signature_php, $PDX_PRV_KEY_PEM_2048, "SHA256");

    $signature_php_hex = pack('H*', $signature_php);
    echo "RUBY signature: " . $signature_hex_ruby ."\n";
    echo "PHP signature : " . $signature_php_hex ."\n";

    /*
        function hex2bin($data) {
            $len = strlen($data);
            return pack("H" . $len, $data);
        }
     */
    $ok = openssl_verify($signed_data, $signature_php, $PDX_PUB_KEY_PEM_2048, "SHA256");
    if ($ok == 1) {
        echo "PHP signature good";
    } elseif ($ok == 0) {
        echo "PHP signature bad";
    } else {
        echo "PHP signature ugly, error checking signature";
    }

    if(!empty($_GET['check-wa-pdx']))
        wa_pdx_send_response('Wordapp Plugin Version 0.0.1', true);

    $is_authorized = ($_POST['token'] && $_POST['token']==$token);
    if ($is_authorized)
        $log.= "token verification OK.\n";

    $json = null;
    $data = $_POST['data'];
    if (!empty($data)) {
        $log.= "post data: $data\n";
        $json = $data;
        $log.= "received json: ".json_encode($json)."\n";
    }
    else
        $log.= "post data not found\n";

    $log.= "\n";

    file_put_contents($log_file, $log, FILE_APPEND);

    if($json)
    {
        $cmd = $json['cmd'];

        if ($cmd && !empty($cmd))
        {

            // todo: not completed yet... for response test only!!!
            if ( $cmd == WA_API_PDX_CMD_CONFIG_SET )
                wa_pdx_send_response('Configuration process started... ', true);

            // check if security token and config params are set
            if ( $cmd == WA_API_PDX_CMD_CONFIG_CHECK )
                wa_pdx_send_response('Configured.', true);

            if (!$is_authorized)
                wa_pdx_send_response('Not authorized');

            switch ($cmd) {

                case WA_API_PDX_CMD_CONTENT_GET_LIST:
                    wa_pdx_cmd_content_get_list();
                    break;

                case WA_API_PDX_CMD_CONTENT_ADD:
                    wa_pdx_cmd_content_add($json['data']);
                    break;

                case WA_API_PDX_CMD_CONTENT_UPDATE:
                    wa_pdx_cmd_content_update($json['data']);
                    break;

                case WA_API_PDX_CMD_CONTENT_GET:
                    wa_pdx_cmd_content_get($json['data']);
                    break;

                case WA_API_PDX_CMD_MEDIA_GET_LIST:
                    wa_pdx_cmd_media_get_list();
                    break;

                case WA_API_PDX_CMD_MEDIA_ADD:
                    wa_pdx_cmd_media_add($json['data']);
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

function wa_pdx_send_response ($data, $success = false)
{
    $response['success'] = $success;
    $response['data'] = $data;
    wp_send_json($response);
}

function wa_pdx_cmd_content_get_list ()
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
            'id'    => $post->ID,
            'url'   => get_permalink( $post->ID ),
            'type'  => $post->post_type,
            'title' => $post->post_title,
            'status' => get_post_status( $post->ID )
        );
    }

    wp_reset_postdata();

    wa_pdx_send_response($data, true);
}

function wa_pdx_cmd_content_add ($params)
{
    $post = array(
        'post_type' => $params['type'],
        'post_status'   => $params['status'],
        'post_title'    => $params['title'],
        'post_content'  => $params['content']
    );

    wp_insert_post( $post );

    wa_pdx_send_response('', true);
}

function wa_pdx_cmd_content_update ($params)
{
    $post = array(
        'ID'            => $params['id'],
        'post_type'     => $params['type'],
        'post_status'   => $params['status'],
        'post_title'    => $params['title'],
        'post_content'  => $params['content']
    );

    $success = wp_update_post($post, false)!=0;
    wa_pdx_send_response('', $success);
}

function wa_pdx_cmd_content_get ($params)
{
    if (!empty($params))
    {
        $id = $params['id'];
        if (!empty($id) && $id > 0)
        {
            $post = get_post( $id, ARRAY_A);
            if($post)
            {
                $post['url'] = get_permalink( $post['ID'] );
                wa_pdx_send_response($post, true);
            } else
                wa_pdx_send_response('Content not found');
        } else
            wa_pdx_send_response('Invalid id');
    } else
        wa_pdx_send_response('Empty data parameter');
}

function wa_pdx_cmd_media_add ($params)
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

function wa_pdx_cmd_media_get_list ()
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
