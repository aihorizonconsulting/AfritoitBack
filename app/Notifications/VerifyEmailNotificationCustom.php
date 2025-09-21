<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotificationCustom extends BaseVerifyEmail
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify', 
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'idU' => $this->user->idU, 
                'hash' => sha1($this->user->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable)
    {
        if (!$notifiable) {
            throw new \Exception('Utilisateur non trouvé pour l’envoi de la vérification.');
        }
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Vérification de votre adresse email')
            ->line('Cliquez sur le bouton ci-dessous pour vérifier votre adresse email.')
            ->action('Vérifier l\'email', $verificationUrl)
            ->line('Si vous n\'avez pas créé de compte, aucune action n\'est requise.');
    }
}
