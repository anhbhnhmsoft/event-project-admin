<?php

namespace App\Utils\Constants;

enum ConfigName: string
{
    case API_KEY               = "API_KEY";
    case CLIENT_ID_APP         = "CLIENT_ID_APP";
    case CHECKSUM_KEY          = "CHECKSUM_KEY";
    case LINK_ZALO_SUPPORT     = "LINK_ZALO_SUPPORT";
    case LINK_FACEBOOK_SUPPORT = "LINK_FACEBOOK_SUPPORT";

        // Zalo Config
    case ZALO_APP_ID           = "ZALO_APP_ID";
    case ZALO_APP_SECRET       = "ZALO_APP_SECRET";
    case ZALO_OA_ID            = "ZALO_OA_ID";
    case ZALO_REDIRECT_URI     = "ZALO_REDIRECT_URI";
    case ZALO_OTP_TEMPLATES    = "ZALO_OTP_TEMPLATES";
}
