<?php

namespace App\Notifications;

use App\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

/**
 * @package : Notifications
 * @author : Hector Alayon
 * @version : 1.0
 */
class CustomResetPassword extends ResetPassword
{
    use Queueable;

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
        $organization = Organization::getOrganizationById($notifiable['organization_id']);
        return (new MailMessage)
            ->subject('Recuperar contraseña')
            ->greeting('Hola')
            ->line('Estás recibiendo este correo porque hiciste una solicitud de recuperación de contraseña para tu 
            cuenta.')
            ->action('Recuperar contraseña',  'http://'.$organization[0]['website'].'/password/reset?token='.
                $this->token)
            ->line('Si no realizaste esta solicitud, no se requiere realizar ninguna otra acción.')
            ->salutation('Saludos, '. config('app.name'));
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
