<?php
/**
 * Media related functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Adds media file to the media library.
 *
 * @api
 *
 * @param array $params Parameters passed from Wordapp.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_media_add ($params)
{
    $jsonFile = $params['file'];
    $alt = $params['alt'];
    $caption = $params['caption'];

    $file_name = $jsonFile['name'];
    $bits = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $jsonFile['body']));

    $parent_post_id = null;

    $upload = wp_upload_bits($file_name, null, $bits);
    if( !empty( $upload['error'] ) )
        wa_pdx_send_response('Media upload error! wp_upload_bits()');

    $file_path = $upload['file'];
    $file_type = wp_check_filetype( $file_name, null );
    $attachment = array(
        'post_excerpt' => $caption,
        'post_mime_type' => $file_type['type'],
        'post_parent' => $parent_post_id,
        'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attachment_id = wp_insert_attachment( $attachment, $file_path, $parent_post_id );
    if (is_wp_error($attachment_id))
        wa_pdx_send_response('Media upload error! wp_insert_attachment()');

    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata( $attachment_id,  $attachment_data );

    if (add_post_meta($attachment_id, '_wp_attachment_image_alt', $alt, true)){
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    }
    wa_pdx_send_response(wp_prepare_attachment_for_js( $attachment_id ), true);
}

/**
 * Downloads media file from url and add to the media library.
 *
 * @api
 *
 * @param array $params Parameters passed from Wordapp.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_media_add_from_url ($params)
{
    $url = $params['url'];
    $alt = $params['alt'];
    $caption = $params['caption'];

    $parent_post_id = null;
    $parts = parse_url($url);
    $file_name = basename($parts['path']);

    if( !class_exists( 'WP_Http' ) )
        include_once( ABSPATH . WPINC . '/class-http.php' );

    $http = new WP_Http();
    $response = $http->request( $url );
    if( $response['response']['code'] != 200 )
        wa_pdx_send_response('Url download error!');

    $bits = $response['body'];

    $upload = wp_upload_bits( $file_name, null, $bits);
    if( !empty( $upload['error'] ) )
        wa_pdx_send_response('Media upload error! wp_upload_bits()');

    $file_path = $upload['file'];
    $file_name = basename( $file_path );
    $file_type = wp_check_filetype( $file_name, null );
    $attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
    $wp_upload_dir = wp_upload_dir();

    $attachment = array(
        'guid'				=> $wp_upload_dir['url'] . '/' . $file_name,
        'post_mime_type'    => $file_type['type'],
        'post_title'	    => $attachment_title,
        'post_content'	    => '',
        'post_status'	    => 'inherit',
        'post_excerpt'      => $caption,
        'post_parent'       => $parent_post_id
    );

    $attachment_id = wp_insert_attachment( $attachment, $file_path, $parent_post_id );
    if (is_wp_error($attachment_id))
        wa_pdx_send_response('Media upload error! wp_insert_attachment()');

    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata( $attachment_id,  $attachment_data );

    if (add_post_meta($attachment_id, '_wp_attachment_image_alt', $alt, true)){
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    }

    wa_pdx_send_response(wp_prepare_attachment_for_js( $attachment_id ), true);
}

/**
 * Retrieves list of items in media library by query criteria.
 *
 * @api
 *
 * @param array $params Query criteria.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_media_list ($params)
{

    $data = array ();
//                $base_url = wp_upload_dir();
//                $base_url = $base_url['baseurl'];

    $args = array();
    if (empty($params)) {
        $args['post_type'] = 'attachment';
        $args['posts_per_page'] = -1;
        $args['post_status'] = 'any';
        $args['post_parent'] = null;
    }
    else {
        // todo: parse params and add to $args
    }
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
