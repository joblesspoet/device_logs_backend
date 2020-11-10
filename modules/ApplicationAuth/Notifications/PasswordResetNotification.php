<?php

namespace Modules\ApplicationAuth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\ApplicationAuth\Entities\User;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends Notification
{
    use Queueable;

    /** @var string */
    private string $token;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $name = $notifiable instanceof User
            ? $notifiable->name
            : trans('application-auth::passwords.mail.generic_recipient');

        return (new MailMessage())
            ->subject(trans('application-auth::passwords.mail.subject'))
            ->greeting(trans('application-auth::passwords.mail.greeting', ['name' => $name]))
            ->line(trans('application-auth::passwords.mail.intro'))
            ->line(trans('application-auth::passwords.mail.token', ['token' => $this->token]))
            ->line(trans('application-auth::passwords.mail.outro1'))
            ->line(trans('application-auth::passwords.mail.outro2', ['name' => config('app.name')]))
            ->salutation(trans('application-auth::passwords.mail.salutation', ['name' => config('app.name')]));
    }

}
