<?php
/**
 * Wordapp plugin constants.
 *
 * @author      Sheroz Khaydarov http://sheroz.com
 * @license     GNU General Public License, version 2
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   Wordapp, 2017
 * @link        https://github.com/sheroz/wp-wordapp-plugin
 * @since       1.0.0
 */

const PDX_OP_ANALYZE_SITE           =   1;
const PDX_OP_WP_CONFIG_SET          =   2;
const PDX_OP_WP_CONFIG_CHECK        =   3;
const PDX_OP_WP_POST_LIST           =   10;
const PDX_OP_WP_POST_ADD            =   11;
const PDX_OP_WP_POST_GET            =   12;
const PDX_OP_WP_POST_UPDATE         =   13;
const PDX_OP_WP_POST_TYPE_LIST      =   14;
const PDX_OP_WP_POST_STATUS_LIST    =   15;
const PDX_OP_WP_POST_TEMPLATE_LIST  =   16;
const PDX_OP_WP_POST_META_GET       =   17;
const PDX_OP_WP_POST_META_UPDATE    =   18;
const PDX_OP_WP_MEDIA_LIST          =   20;
const PDX_OP_WP_MEDIA_ADD           =   21;
const PDX_OP_WP_MEDIA_ADD_FROM_URL  =   22;
const PDX_OP_WP_USER_LIST           =   30;
const PDX_OP_WP_PREPARE_PREVIEW     =   40;
const PDX_OP_WP_CHECK_PLUGIN        =   50;
const PDX_OP_WP_SLIMSTAT_TOKEN      =   60;
const PDX_OP_WP_POST_KEYWORDS       =   61;
const PDX_OP_WP_POST_FREQUENCY      =   70;
const PDX_OP_WP_LAST_UPDATED_POST   =   71;
const PDX_OP_WP_ADMIN_ACCESS_URL    =   80;
const PDX_OP_WP_PLUGIN_UPDATE       =   90;
const PDX_OP_WP_PLUGIN_LIST         =   91;
const PDX_OP_WP_PLUGIN_INSTALL      =   92;
const PDX_OP_WP_PLUGIN_ACTIVATE     =   93;
const PDX_OP_WP_PLUGIN_DEACTIVATE   =   94;
const PDX_OP_WP_PLUGIN_DELETE       =   95;

const PDX_PLUGIN_VERSION_NUMBER = '1.4.1';

const PDX_SETTINGS_PAGE_SLUG    = 'wa_pdx';
const PDX_SETTINGS_OPTION_NAME  = 'wa_pdx_options';
const PDX_CONFIG_OPTION_KEY     = 'wa_pdx_config';
const PDX_CONFIG_SCRIPTS_KEY    = 'wa_pdx_scripts';
const PDX_SCHEDULED_OPTION_KEY  = 'wa_pdx_future';
const PDX_PLUGIN_PATH           = 'wordapp/wordapp.php';

const PDX_PUB_KEY_PEM_2048      = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApMxOSkfyRShmTseKyRbt\noi1qSLXPvRjr31cdNf4LFfxGAnpUui+ggaMkg5quMHkSflKleOFG2heWtbnXrTEK\npdcvW/SFjGSsoFIm90sHG0onuPNhC4cP4SSLuczsg3TDIpsl+XJJnXFFM+gHiEa8\ngRh/BXZEG5I4pUIN3xHkl34vFgyFA6WphQjRx+FhPPakEozf3dShAWaFS26FKBbJ\nXRW4cUBtvtbpPIh/MTU8Mna4h/cSzp/3AJB2WLvytuT2jA01DYUVjc3d8tYR+XAt\nJZ00+k75fM/lINrcJMrlfebb27hPFpoB/1YTdsUa7tpAnLTXDPBkqD2kXb6F2963\nYwIDAQAB\n-----END PUBLIC KEY-----\n";

// parameter: PDX_CONFIG_VALIDATE_SIGNATURE
// values: 1 - validate sender by digital signature check, 0 = do not validate by signature
const PDX_CONFIG_VALIDATE_SIGNATURE = 1;

// parameter: PDX_CONFIG_VALIDATE_IP
// values: 1 - validate sender by IP address, 0 = do not validate IP
const PDX_CONFIG_VALIDATE_IP = 0;

// a space separated list of trusted IPs the plugin allowed to receive configuration data
const PDX_CONFIG_SERVER_IP = '54.246.232.229';
// const PDX_CONFIG_SERVER_IP = '54.246.232.229 34.253.239.202 52.16.70.35 ';

// parameter: PDX_CONFIG_PUSH
// values: 1 - server push configuration, 0 = use a preferred and secure ticket based mechanism
// Works only for fresh install without prior configuration
// After completing configuration process, set it back to 0
const PDX_CONFIG_PUSH = 1;

// parameter: PDX_CONFIG_SCHEDULE_PUBLISH_MISSED
// values: 1 - Looks for scheduled posts with missed date and publishes them, 0 = disable check
const PDX_CONFIG_SCHEDULE_PUBLISH_MISSED = 1;

// parameter:  PDX_LOG_ENABLE
// values: 1 - enable plugin logs, 0 - disable logs
const PDX_LOG_ENABLE = 0;

if (PDX_LOG_ENABLE != 0)
{
    $upload_dir = wp_upload_dir(null, false);
    define('PDX_LOG_FILE', $upload_dir["basedir"] . '/wordapp-plugin.log'); 
}

const PDX_MARKER_CONTENT_BEGIN  = '<!-- Wordapp-Marker-Begin: Content -->';
const PDX_MARKER_CONTENT_END    = '<!-- Wordapp-Marker-End: Content -->';

const PDX_MARKER_BLOCK_BEGIN    = '<!-- Wordapp-Marker-Begin: {} -->';
const PDX_MARKER_BLOCK_END      = '<!-- Wordapp-Marker-End: {} -->';

/*

content mapper structure

# this is a comment line
# first mapper
WA_CONTENT_MAPPER [block-1]
<style>
    .class-wa-block1 {
        h1 {};
        p {};
        img {};
    }
    .class-wa-block1-description {};
</style>
<div class='class-wa-block1'>
    <h1>{{text[0]}}</h1>
    <p>{{text[1]}}</p>
    <div class='class-wa-block1-description'>
        <img src='{{url[3]}}' alt ='{{alt[3]}}'>
        <p>{{text[2]}}</p>
    </div>
</div>
WA_CONTENT_MAPPER_END

# this is
# second mapper
WA_CONTENT_MAPPER [page-footer2]
<p style='color:red;'>See you, {{text[3]}}</p>
WA_CONTENT_MAPPER_END

*/
