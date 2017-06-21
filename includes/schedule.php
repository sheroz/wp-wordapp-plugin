<?php
/**
 * Wordkaround for 'missed schedule' cases
 *
 * @author      Sheroz Khaydarov <sheroz@wordapp.io>
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        http://wordapp.io
 * @since       1.2.1
 */

/**
 * Workaround to fix Wordpress's 'missed schedule' error.
 *
 * Looks for scheduled posts with missed date and publishes them
 * Next call will be validated after 15 minutes to prevent extra loads for hosting server
 */

function wa_pdx_validate_scheduled_posts ()
{
    if (get_transient(PDX_SCHEDULED_OPTION_KEY) === false) {

        global $wpdb;
        $sql = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = 'future' AND post_date > 0 AND post_date <= %s",
            current_time( 'mysql', 0 ) );

        $scheduled_ids = $wpdb->get_col( $sql, 0 );

        if (count($scheduled_ids) )
        {
            foreach ( $scheduled_ids as $post_id )
            {
                if ($post_id)
                    wp_publish_post($post_id);
            }
        }

        set_transient( PDX_SCHEDULED_OPTION_KEY, 1, 15 * MINUTE_IN_SECONDS );
    }
}
