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

    return (new \Illuminate\Notifications\Messages\MailMessage)
                ->view('emails.reset-password', [
                    'url' => $url,
                    'user' => $notifiable
                ]);
}

}
