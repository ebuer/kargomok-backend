<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;
    /**
     * Şifre sıfırlama e-postası. Özel şablon: resources/views/emails/reset-password.blade.php
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $frontUrl = config('app.front_url', config('app.url'));
        $url = rtrim($frontUrl, '/') . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expireMinutes = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Şifre Sıfırlama - ' . config('app.name'))
            ->markdown('emails.reset-password', [
                'url' => $url,
                'expireMinutes' => $expireMinutes,
            ]);
    }
}
