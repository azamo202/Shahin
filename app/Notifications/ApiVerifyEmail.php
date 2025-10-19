<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Messages\MailMessage;

class ApiVerifyEmail extends VerifyEmailBase
{
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify', // اسم الراوت الذي سنعرفه كـ API route
            now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);
        return (new MailMessage)
                    ->subject('تحقق من بريدك')
                    ->line('اضغط الرابط لإتمام التحقق من بريدك.')
                    ->action('تحقق الآن', $url);
    }
    
}
