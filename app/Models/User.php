<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // ... existing code (fillable, hidden, etc.)

    /**
     * Determine if the user can access the Filament panel.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        // Se l'utente ha un avatar_url (da Google), usa quello, altrimenti null
        return $this->avatar_url;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // For local development, you might allow everyone.
        // In production, you might use: return str_ends_with($this->email, '@yourdomain.com');
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',  // Non dimenticare questo se vuoi l'avatar!
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
        ];
    }
}
