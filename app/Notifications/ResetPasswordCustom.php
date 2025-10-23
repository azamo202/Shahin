<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCustom extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url');
        $url = "{$frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->email);

        return (new MailMessage)
                    ->subject('إعادة تعيين كلمة المرور')
                    ->line('لقد استلمنا طلبك لإعادة تعيين كلمة المرور.')
                    ->action('إعادة تعيين كلمة المرور', $url)
                    ->line('إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذا البريد.');
    }
}
