<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends ResetPassword
{

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage)
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->line(__('To reset your password follow these steps').':')
            ->line(__('1. Open the app'))
            ->line(__('2. Go to the reset password form'))
            ->line(__('3. Enter your e-mail address'))
            ->line(__('4. Enter the following reset token').': '.$this->token)
            ->line(__('5. Enter your (new) password'))
            ->line(__('The reset token will remain valid for one hour after it has been requested, if more than one hour passes you must request a new reset token.'))
            ->line(__('If you did not request a password reset, no further action is required.'));
    }

}
