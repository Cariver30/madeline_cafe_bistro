<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'invitation_token',
        'invitation_sent_at',
        'invitation_accepted_at',
        'active',
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invitation_sent_at' => 'datetime',
            'invitation_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isServer(): bool
    {
        return $this->role === 'server';
    }

    public function isPos(): bool
    {
        return $this->role === 'pos';
    }

    public function hasRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : func_get_args();

        return in_array($this->role, $roles, true);
    }

    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function tableSessions()
    {
        return $this->hasMany(TableSession::class, 'server_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'server_id');
    }
}
