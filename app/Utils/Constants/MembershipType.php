<?php

namespace App\Utils\Constants;

enum MembershipType: int
{
    case FOR_ORGANIZER = 1;
    case FOR_CUSTOMER  = 2;
}
