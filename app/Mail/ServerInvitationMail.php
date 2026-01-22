<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ServerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $token;
    public string $roleLabel;
    public string $roleDescription;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $token, ?string $roleLabel = null, ?string $roleDescription = null)
    {
        $this->user = $user;
        $this->token = $token;
        $this->roleLabel = $roleLabel ?? 'mesero';
        $this->roleDescription = $roleDescription ?? 'gestionar mesas y el programa de fidelidad';
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

        $subject = $this->roleLabel === 'POS'
            ? 'Activa tu acceso POS'
            : "Activa tu acceso de {$this->roleLabel}";

        return $this->subject($subject)
            ->view('emails.loyalty.server-invitation')
            ->with([
                'user' => $this->user,
                'url' => $url,
                'roleLabel' => $this->roleLabel,
                'roleDescription' => $this->roleDescription,
            ]);
    }
}
