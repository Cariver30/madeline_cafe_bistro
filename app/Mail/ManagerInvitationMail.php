<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManagerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $token;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $url = route('loyalty.invitations.show', [
            'email' => $this->user->email,
            'token' => $this->token,
        ]);

        return $this->subject('Activa tu acceso como gerente')
            ->view('emails.admin.manager-invitation')
            ->with([
                'user' => $this->user,
                'url' => $url,
            ]);
    }
}
