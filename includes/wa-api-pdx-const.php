<?php
/**
 * Author: Sheroz Khaydarov
 * Date: 20/03/2017
 * Time: 08:29
 */

const PDX_OP_ANALYZE_SITE       =   1;
const PDX_OP_CONFIG_CHECK       =   2;
const PDX_OP_CONFIG_SET         =   3;
const PDX_OP_CONTENT_GET_LIST   =   4;
const PDX_OP_CONTENT_ADD        =   5;
const PDX_OP_CONTENT_GET        =   6;
const PDX_OP_CONTENT_UPDATE     =   7;
const PDX_OP_MEDIA_GET_LIST     =   8;
const PDX_OP_MEDIA_ADD          =   9;
const PDX_OP_MEDIA_ADD_FROM_URL =   10;
const PDX_OP_MEDIA_UPDATE       =   11;
const PDX_OP_PREPARE_PREVIEW    =   12;

const PDX_PLUGIN_VERSION_NUMBER  =  '0.2.2';
const PDX_PLUGIN_VERSION_TEXT  =  'Wordapp Plugin Version ' . PDX_PLUGIN_VERSION_NUMBER;
const PDX_CONFIG_OPTION_KEY = 'wa_pdx_config';

const PDX_PUB_KEY_PEM_2048   = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApMxOSkfyRShmTseKyRbt\noi1qSLXPvRjr31cdNf4LFfxGAnpUui+ggaMkg5quMHkSflKleOFG2heWtbnXrTEK\npdcvW/SFjGSsoFIm90sHG0onuPNhC4cP4SSLuczsg3TDIpsl+XJJnXFFM+gHiEa8\ngRh/BXZEG5I4pUIN3xHkl34vFgyFA6WphQjRx+FhPPakEozf3dShAWaFS26FKBbJ\nXRW4cUBtvtbpPIh/MTU8Mna4h/cSzp/3AJB2WLvytuT2jA01DYUVjc3d8tYR+XAt\nJZ00+k75fM/lINrcJMrlfebb27hPFpoB/1YTdsUa7tpAnLTXDPBkqD2kXb6F2963\nYwIDAQAB\n-----END PUBLIC KEY-----\n";

// Digital signature check
// parameter: PDX_SIGNATURE_CHECK
// values: 1 - signature check, 0 = do not check signature
const PDX_SIGNATURE_CHECK = 1;

// Logging
// parameter:  PDX_LOG_ENABLE
// values: 1 - logs to PDX_LOG_FILE, 0 - disable logs
const PDX_LOG_ENABLE = 0;
const PDX_LOG_FILE = '/tmp/wordapp-seo-debug.log';

