<?php

namespace App\Utils\Constants;

enum ConfigName: string
{
    case LOGO = "LOGO";
    case FACEBOOK = "FACEBOOK";
    case YOUTUBE = "YOUTUBE";
    case TIKTOK = "TIKTOK";
    case INSTAGRAM = "INSTAGRAM";
    case FOOTER_COPYRIGHT = "FOOTER_COPYRIGHT";
    case MANAGING_UNIT = "MANAGING_UNIT";

    case API_KEY       = "API_KEY";
    case CLIENT_ID_APP = "CLIENT_ID_APP";
    case CHECKSUM_KEY  = "CHECKSUM_KEY";
}
