<?php
/**
 * Post meta functions.
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.0.0
 */

/**
 * Post meta get operation.
 *
 * @api
 *
 * @param array $params Operation parameters passed from Wordapp Platform.
 *
 * @return mixed JSON that indicates success/failure status
 *               of the operation in 'success' field,
 *               and an appropriate 'data' or 'error' fields.
 */
function wa_pdx_op_meta_get ($params)
{
    if (empty($params))
        wa_pdx_send_response('Empty data parameter');

    $content_id = $params['content_id'];
    if (!empty($content_id) && $content_id > 0)
    {
        //$fields = get_post_custom($content_id);
        //wa_pdx_send_response($fields, true);
        //$meta = get_metadata('post', $content_id);

        $meta = get_post_meta($content_id, '', true);
        if (is_array($meta))
        {
            $out = array();
            foreach($meta as $k => $v) {
                if (is_array($v))
                {
                    $r = array();
                    foreach($v as $k2 => $v2) {
                        $r[$k2] = maybe_unserialize($v2);
                    }
                    $out[$k] = $r;
                }
                else
                    $out[$k] = maybe_unserialize($v);
            }
            $meta = $out;
        }
        wa_pdx_send_response($meta, true);
    }
    else
        wa_pdx_send_response('Invalid content id');
}

/**
 * Post meta update operation.
 *
 * @api
 *
 * @param array $params Operation parameters passed from Wordapp Platform.
 *
 * @return mixed JSON that indicates success/failure of the operation,
 *                or JSON that indicates an error occurred.
 */
function wa_pdx_op_meta_update ($params)
{
    if (empty($params))
        wa_pdx_send_response('Empty data parameter');

    $content_id = $params['content_id'];
    if (!empty($content_id) && $content_id > 0)
    {
        $i_meta = $params['meta'];
        $meta = array();
        foreach($i_meta as $k => $v) {
            $k = trim(str_replace(array('[',']'),' ', trim($k)));
            $t = explode(' ', $k );
            if(is_array($t) && count($t)==2)
            {
                if (!isset($meta[$t[0]]))
                    $meta[$t[0]] = array();

                if (is_array($v)) {
                    $meta[$t[0]][$t[1]] = array();
                    foreach ($v as $a_k => $a_v)
                        $meta[$t[0]][$t[1]][$a_k] = $a_v;
                }
                else
                    $meta[$t[0]][$t[1]] = $v;
            }
            else {
                if (is_array($v)) {
                    $meta[$k] = array();
                    foreach ($v as $a_k => $a_v) {
                        $meta[$k][$a_k] = $a_v;
                    }
                }
                else
                    $meta[$k] = $v;
            }
        }

        if (PDX_LOG_ENABLE)
        {
            $log = "wa_pdx_op_meta_update (), meta dump:\n--- Begin print_r():\n".print_r($meta, true)."\n--- End print_r():\n";
            file_put_contents(PDX_LOG_FILE, $log, FILE_APPEND);
        }

        $skipped = array();
        foreach($meta as $k => $v) {
            // take a care for other non provided fields
            // non provided fields should stay as is
            $old = get_post_meta($content_id, $k, true);
            if (is_array($old) && is_array($v))
            {
                foreach($old as $k_o => $v_o) {
                    if (!isset($v[$k_o])) {
                        $v[$k_o] = $v_o;
                    }
                }
            }

            if (!update_post_meta($content_id, $k, $v))
                $skipped[] = $k;
        }

        $result = 'OK';
        if (!empty($skipped)) {
            $result .= '. Skipped field(s): ';
            foreach($skipped as $v)
                $result .= $v.' ';
        }

        wa_pdx_send_response($result, true);
    }
    else
        wa_pdx_send_response('Invalid content id');
}
