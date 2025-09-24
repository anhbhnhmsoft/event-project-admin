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

    case ADMIN_ACCOUNT_BANK_NUMBER = "ADMIN_ACCOUNT_BANK_NUMBER";
    case ADMIN_ACCOUNT_BANK_NAME   = "ADMIN_ACCOUNT_BANK_NAME";
    case ADMIN_ACCOUNT_BANK_BIN    = "ADMIN_ACCOUNT_BANK_BIN";

    case API_KEY       = "API_KEY";
    case CLIENT_ID_APP = "CLIENT_ID_APP";
    case CHECKSUM_KEY  = "CHECKSUM_KEY";
}
