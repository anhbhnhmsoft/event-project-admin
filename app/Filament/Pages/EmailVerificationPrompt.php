<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\SendsPublicEmailVerification;
use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
{
    use SendsPublicEmailVerification;
}
