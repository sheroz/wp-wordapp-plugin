<?php
/**
 * Configuration related functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Clears configuration data.
 * Removes Wordapp plugin related configuration data from the WordPress option storage. Called internally when plugin activated, deactivated or uninstalled.
 *
 * @internel
 */
function wa_pdx_config_clear()
{
    delete_option( PDX_CONFIG_OPTION_KEY );
    if (PDX_LOG_ENABLE)
    {
        $log = "Plugin Configuration Removed.\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }
}

/**
 * Checks configuration data.
 *
 * @return mixed The non-confidential information about plugin status
 */
function wa_pdx_op_config_check()
{
    global $wp_version;
    $cfg = get_option( PDX_CONFIG_OPTION_KEY );
    wa_pdx_send_response(array (
        'configured'        => !empty($cfg),
        'ajax-url'          => admin_url('admin-ajax.php'),
        'plugin-version'    => PDX_PLUGIN_VERSION_NUMBER,
        'wp-version'        => $wp_version
    ), true);
}

/**
 * Sets configuration.
 * This operation is initiated from Wordapp Platform to configure plugin for further data exchange operations.
 *
 * @api
 *
 * @param array $params The operation parameters passed from Wordapp Platform.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_config_set ($params)
{
    if (PDX_LOG_ENABLE) {
        $log = "--- PDX_OP_CONFIG_SET ---\n";
        file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
    }

    if (empty($params))
        wa_pdx_send_response('Invalid Data');

    $ticket_id   = $params['ticket_id'];
    $api_pdx_url = $params['api_pdx_url'];
    $timestamp   = $params['timestamp'];
    $signature   = $params['signature'];

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

    $wa_pdx_options = wa_pdx_get_options();

    if ($wa_pdx_options['validate_signature'])
    {
        $reassembled_data = sprintf("%02x", PDX_OP_WP_CONFIG_SET);
        $reassembled_data .= $ticket_id;
        $reassembled_data .= $api_pdx_url;
        $reassembled_data .= dechex($timestamp);

        $reassembled_data = strtolower ( $reassembled_data);

        $signature = pack("H" . strlen($signature), $signature);
        $verified = openssl_verify($reassembled_data, $signature, PDX_PUB_KEY_PEM_2048, "SHA256");

        if (PDX_LOG_ENABLE)
        {
            $log = "Reassembled data: " . $reassembled_data ."\n";
            if ($verified == 1) {
                $log.= "Signature verification result: OK.\n";
            } elseif ($verified == 0) {
                $log.= "Signature verification result: FAILED.\n";
            } else {
                $log.= "Signature verification result: Unknown Error.\n";
            }
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }

        if ( $verified != 1 ) {
            wa_pdx_send_response('Signature verification failed.');
        }
    }

    if ($wa_pdx_options['validate_ip'])
    {
        $sender = $_SERVER['REMOTE_ADDR'];
        $ips = explode(' ', $wa_pdx_options['server_ip']);
        $found = false;
        foreach ($ips as $ip) {
            if ($found = ($sender==$ip))
                break;
        }
        if (!$found)
            wa_pdx_send_response('IP Verification Failed:' . $_SERVER['REMOTE_ADDR']);
    }

    if (PDX_CONFIG_PUSH == 1)
    {
        $pdx_config = $params['config'];
        if (empty($pdx_config))
            wa_pdx_send_response('Invalid configuration parameter.');

        update_option( PDX_CONFIG_OPTION_KEY, $pdx_config );

        if (PDX_LOG_ENABLE)
        {
            $log = "direct config set: " . json_encode($pdx_config) ."\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }
        wa_pdx_send_response('Configuration set successfully', true);
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
            'User-Agent'   => 'Wordapp Plugin Version ' . PDX_PLUGIN_VERSION_NUMBER,
            'X-Api-Version' => 'application/vnd.wordapp-v1+json'
        ),
        'timeout' => 5,
        'blocking' => true,
        'body' => json_encode(array( 'ticket_id' => $ticket_id ))
    );

//  $http = new WP_Http();
//  $response = $http->request($api_pdx_tickets_url, $args);

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
    wa_pdx_send_response('Configuration set successfully', true);

}
