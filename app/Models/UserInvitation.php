<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'invited_by',
        'email',
        'role',
        'token',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the company that owns the invitation.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation is still valid (not accepted and not expired).
     */
    public function isValid(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }

    /**
     * Generate a unique invitation token.
     */
    public static function generateToken(): string
    {
        return Str::random(60);
    }

    /**
     * Create a new invitation.
     */
    public static function createInvitation(array $data): self
    {
        return static::create([
            'company_id' => $data['company_id'],
            'invited_by' => $data['invited_by'],
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => static::generateToken(),
            'expires_at' => now()->addDays(7), // Invitations expire in 7 days
        ]);
    }

    /**
     * Accept the invitation.
     */
    public function accept(): void
    {
        $this->accepted_at = now();
        $this->save();
    }
}
