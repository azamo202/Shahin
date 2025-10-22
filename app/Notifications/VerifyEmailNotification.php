<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class VerifyEmailNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    protected function verificationUrl($notifiable)
    {
        // مدة صلاحية الرابط: 60 دقيقة — غيّر حسب حاجتك
        $expiration = Carbon::now()->addMinutes(60);

        return URL::temporarySignedRoute(
            'verification.verify',
            $expiration,
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        // نستخدم view لتصميم HTML مخصص (سأعطي الـ blade بعد قليل)
        return (new MailMessage)
                    ->subject('تأكيد بريدك الإلكتروني')
                    ->view('emails.verify', [
                        'user' => $notifiable,
                        'url'  => $url,
                    ]);
    }
}
