<?php
/**
 * User: sheroz
 * Date: 20/03/2017
 * Time: 08:29
 */

const WA_API_PDX_CMD_CONFIG_CHECK       =   1;
const WA_API_PDX_CMD_CONFIG_SET         =   2;
const WA_API_PDX_CMD_CONTENT_GET_LIST   =   3;
const WA_API_PDX_CMD_CONTENT_ADD        =   4;
const WA_API_PDX_CMD_CONTENT_GET        =   5;
const WA_API_PDX_CMD_CONTENT_UPDATE     =   6;
const WA_API_PDX_CMD_MEDIA_GET_LIST     =   7;
const WA_API_PDX_CMD_MEDIA_ADD          =   8;
const WA_API_PDX_CMD_MEDIA_UPDATE       =   9;

const PDX_PUB_KEY_PEM_2048  = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApMxOSkfyRShmTseKyRbt\noi1qSLXPvRjr31cdNf4LFfxGAnpUui+ggaMkg5quMHkSflKleOFG2heWtbnXrTEK\npdcvW/SFjGSsoFIm90sHG0onuPNhC4cP4SSLuczsg3TDIpsl+XJJnXFFM+gHiEa8\ngRh/BXZEG5I4pUIN3xHkl34vFgyFA6WphQjRx+FhPPakEozf3dShAWaFS26FKBbJ\nXRW4cUBtvtbpPIh/MTU8Mna4h/cSzp/3AJB2WLvytuT2jA01DYUVjc3d8tYR+XAt\nJZ00+k75fM/lINrcJMrlfebb27hPFpoB/1YTdsUa7tpAnLTXDPBkqD2kXb6F2963\nYwIDAQAB\n-----END PUBLIC KEY-----\n";
const PDX_SIGNATURE_CHECK_STRICT = 1; // 1 - strict check, 0 = continue even verification failed
