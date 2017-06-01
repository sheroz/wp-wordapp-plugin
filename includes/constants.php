<?php
/**
 * Author: Sheroz Khaydarov <sheroz@wordapp.io>
 * Date: 20/03/2017 Time: 08:29
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

const PDX_PLUGIN_VERSION_NUMBER = '1.0.1';
const PDX_PLUGIN_VERSION_TEXT   = 'Wordapp Plugin Version ' . PDX_PLUGIN_VERSION_NUMBER;
const PDX_CONFIG_OPTION_KEY     = 'wa_pdx_config';

const PDX_MARKER_CONTENT_BEGIN  = '<!-- Wordapp-Marker-Begin: Content -->';
const PDX_MARKER_CONTENT_END    = '<!-- Wordapp-Marker-End: Content -->';

const PDX_MARKER_BLOCK_BEGIN    = '<!-- Wordapp-Marker-Begin: {} -->';
const PDX_MARKER_BLOCK_END      = '<!-- Wordapp-Marker-End: {} -->';

const PDX_PUB_KEY_PEM_2048   = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApMxOSkfyRShmTseKyRbt\noi1qSLXPvRjr31cdNf4LFfxGAnpUui+ggaMkg5quMHkSflKleOFG2heWtbnXrTEK\npdcvW/SFjGSsoFIm90sHG0onuPNhC4cP4SSLuczsg3TDIpsl+XJJnXFFM+gHiEa8\ngRh/BXZEG5I4pUIN3xHkl34vFgyFA6WphQjRx+FhPPakEozf3dShAWaFS26FKBbJ\nXRW4cUBtvtbpPIh/MTU8Mna4h/cSzp/3AJB2WLvytuT2jA01DYUVjc3d8tYR+XAt\nJZ00+k75fM/lINrcJMrlfebb27hPFpoB/1YTdsUa7tpAnLTXDPBkqD2kXb6F2963\nYwIDAQAB\n-----END PUBLIC KEY-----\n";

// parameter: PDX_SIGNATURE_CHECK
// values: 1 - digital signature check, 0 = do not check signature
const PDX_SIGNATURE_CHECK = 1;

// parameter:  PDX_LOG_ENABLE
// values: 1 - logs to PDX_LOG_FILE, 0 - disable logs
const PDX_LOG_ENABLE = 0;
const PDX_LOG_FILE = '/var/www/html/tmp/wordapp-seo.log';

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
