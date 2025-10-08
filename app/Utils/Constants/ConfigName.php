<?php

namespace App\Utils\Constants;

enum ConfigName: string
{
    case API_KEY               = "API_KEY";
    case CLIENT_ID_APP         = "CLIENT_ID_APP";
    case CHECKSUM_KEY          = "CHECKSUM_KEY";
    case LINK_ZALO_SUPPORT     = "LINK_ZALO_SUPPORT";
    case LINK_FACEBOOK_SUPPORT = "LINK_FACEBOOK_SUPPORT";
}
