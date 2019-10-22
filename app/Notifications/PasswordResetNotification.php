<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\ConnectMail;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token;

    protected $title = 'パスワードリセット 通知';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail_to = $notifiable->email;
        $subject = "パスワードリセット";
        $reset_url = url('password/reset', $this->token);

        $options = [
            'subject' => $subject,
            'template' => 'mail.passwordreset',
        ];

        $data = [
            'reset_url' => $reset_url,
        ];

        return (new ConnectMail($options, $data))->to($mail_to);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
