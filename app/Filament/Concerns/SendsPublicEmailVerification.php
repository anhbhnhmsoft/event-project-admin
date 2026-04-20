<?php

namespace App\Filament\Concerns;

use App\Mail\VerifyEmailMail;
use App\Utils\Constants\Language;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

trait SendsPublicEmailVerification
{
    protected function sendEmailVerificationNotification(MustVerifyEmail | Model $user): void
    {
        if (! $user instanceof MustVerifyEmail) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ],
        );

        $locale = $user->lang ?? app()->getLocale();

        if ($locale instanceof Language) {
            $locale = $locale->value;
        }

        Mail::to($user->email)->queue(new VerifyEmailMail($url, (string) $locale, $user));
    }
}
