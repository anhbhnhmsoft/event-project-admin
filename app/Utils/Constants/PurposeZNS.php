<?php

namespace App\Utils\Constants;

enum PurposeZNS: int
{
    case REGISTER = 1;
    case FORGOT_PASSWORD = 2;
    case VERIFY_PHONE = 3;
}
