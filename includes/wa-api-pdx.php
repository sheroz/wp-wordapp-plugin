<?php
/**
 * User: sheroz
 * Date: 20/03/2017
 * Time: 08:29
 */

require_once 'wa-api-pdx-const.php';

function ajax_wordapp_seo() {

    $token = 'q*ZNLR9+3s!cjfstz.@&KBY@4AerUc36';

    $file = '/tmp/wordapp-seo-debug.log';

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

    $response = array(
        'success' => false,
        'data'	=> null
    );

    if(!empty($_GET['check-wordapp-seo']))
    {
        $response['success'] = true;
        $response['data'] = 'Wordapp-SEO Version 0.0.1';
        wp_send_json($response);
    }

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

    file_put_contents($file, $log, FILE_APPEND);

    $cmd = null;
    $success = false;
    $data = null;

    if($json)
    {
        $cmd = $json['cmd'];

        if ($cmd && !empty($cmd))
        {

            if ( $cmd == WA_API_PDX_CMD_CONFIG_SET ) {
                $response['success'] = true;
                $response['data'] = 'Configuration process started... '; // todo: test only!!!
                wp_send_json($response);
            }

            if ( $cmd == WA_API_PDX_CMD_CONFIG_CHECK )
            {
                // check if security token and config params are set
                $response['success'] = true;
                $response['data'] = 'Configured.';
                wp_send_json($response);
            } else
            {
                if (!$is_authorized) {
                    $response['success'] = false;
                    $response['data'] = 'Not authorized';
                    wp_send_json($response);
                }
            }

            if ( $cmd == WA_API_PDX_CMD_CONTENT_GET_LIST )
            {

                $args = array(
                    'posts_per_page' => -1,
                    'orderby' => array('type','ID'),
                    'post_type' => array('post','page'),
                    'post_status' => 'publish,pending,draft,private,trash'
                );
//              'post_status' => 'publish,pending,draft,auto-draft,future,private,inherit,trash'


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

                $success = true;
            }
            else if ( $cmd == WA_API_PDX_CMD_CONTENT_ADD )
            {
                $params = $json['data'];

                $post = array(
                    'post_type' => $params['type'],
                    'post_status'   => $params['status'],
                    'post_title'    => $params['title'],
                    'post_content'  => $params['content']
                );

                wp_insert_post( $post );

                $success = true;
                $data = '';
            }
            else if ( $cmd == WA_API_PDX_CMD_CONTENT_UPDATE )
            {
                $params = $json['data'];

                $post = array(
                    'ID'            => $params['id'],
                    'post_type'     => $params['type'],
                    'post_status'   => $params['status'],
                    'post_title'    => $params['title'],
                    'post_content'  => $params['content']
                );

                $success = wp_update_post($post, false)!=0;
                $data = '';
            }
            else if ( $cmd == WA_API_PDX_CMD_CONTENT_GET )
            {
                $params = $json['data'];
                if (!empty($params))
                {
                    $id = $params['id'];
                    if (!empty($id) && $id > 0)
                    {
                        $post = get_post( $id, ARRAY_A);
                        if($post)
                        {
                            $success = true;
                            $post['url'] = get_permalink( $post['ID'] );
                            $data = $post;
                        } else
                            $data = 'Content not found';
                    } else
                        $data = 'Invalid id';
                } else
                    $data = 'Empty data parameter';
            }
            else if ( $cmd == WA_API_PDX_CMD_MEDIA_GET_LIST )
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
                $success = true;
            }
            else if ( $cmd == WA_API_PDX_CMD_MEDIA_ADD )
            {
                $params = $json['data'];

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

                        $success = true;
                        $data = '';

                    } else
                        $data = 'File upload error! wp_insert_attachment()';
                } else
                    $data = 'File upload error! wp_upload_bits()';
            }
            else
                $data = 'No valid command';
        }
        else
            $data = 'No command found';
    }
    else
        $data = 'Invalid Data';

    $response['success'] = $success;
    $response['data'] = $data;
    wp_send_json($response);
}
