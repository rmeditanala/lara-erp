<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class CustomerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'job_title',
        'department',
        'is_primary',
        'is_active',
        'notes',
        'custom_fields',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'custom_fields' => 'array',
    ];

    /**
     * Get the customer that owns the contact.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all activities for this contact.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the primary email attribute.
     */
    public function getPrimaryEmailAttribute(): ?string
    {
        return $this->email ?: $this->customer?->email;
    }

    /**
     * Get the primary phone attribute.
     */
    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->phone ?: $this->mobile ?: $this->customer?->phone;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one primary contact per customer
        static::saving(function ($contact) {
            if ($contact->is_primary) {
                static::where('customer_id', $contact->customer_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }
        });

        // Record activity when contact is created
        static::created(function ($contact) {
            Activity::createForSubject(
                $contact,
                'contact_added',
                "Contact Created: {$contact->full_name}",
                "New contact {$contact->full_name} was added for {$contact->customer->display_name}",
                [
                    'contact_name' => $contact->full_name,
                    'customer_name' => $contact->customer->display_name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                ],
                auth()->user()
            );
        });

        // Record activity when contact is updated
        static::updated(function ($contact) {
            $changes = $contact->getDirty();

            if (isset($changes['is_primary']) && $contact->is_primary) {
                Activity::createForSubject(
                    $contact,
                    'primary_set',
                    "Primary Contact Set: {$contact->full_name}",
                    "{$contact->full_name} was set as the primary contact for {$contact->customer->display_name}",
                    [
                        'contact_name' => $contact->full_name,
                        'customer_name' => $contact->customer->display_name,
                    ],
                    auth()->user()
                );
            } elseif (isset($changes['is_primary']) && !$contact->is_primary) {
                Activity::createForSubject(
                    $contact,
                    'primary_removed',
                    "Primary Contact Removed: {$contact->full_name}",
                    "{$contact->full_name} was removed as the primary contact for {$contact->customer->display_name}",
                    [
                        'contact_name' => $contact->full_name,
                        'customer_name' => $contact->customer->display_name,
                    ],
                    auth()->user()
                );
            } elseif (isset($changes['is_active'])) {
                $activityType = $contact->is_active ? 'contact_activated' : 'contact_deactivated';
                $action = $contact->is_active ? 'activated' : 'deactivated';

                Activity::createForSubject(
                    $contact,
                    $activityType,
                    "Contact {$action}: {$contact->full_name}",
                    "{$contact->full_name} was {$action} for {$contact->customer->display_name}",
                    [
                        'contact_name' => $contact->full_name,
                        'customer_name' => $contact->customer->display_name,
                        'status' => $contact->is_active ? 'active' : 'inactive',
                    ],
                    auth()->user()
                );
            } else {
                Activity::createForSubject(
                    $contact,
                    'contact_updated',
                    "Contact Updated: {$contact->full_name}",
                    "Contact information for {$contact->full_name} was updated",
                    array_intersect_key($contact->getDirty(), array_flip([
                        'first_name', 'last_name', 'email', 'phone', 'mobile', 'job_title', 'department'
                    ])),
                    auth()->user()
                );
            }
        });
    }

    /**
     * Scope to only include active contacts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get primary contacts.
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to search contacts by name, email, or phone.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('job_title', 'like', "%{$search}%");
        });
    }

    /**
     * Make this contact the primary contact for the customer.
     */
    public function makePrimary(): bool
    {
        // Remove primary status from other contacts
        static::where('customer_id', $this->customer_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this contact as primary
        return $this->update(['is_primary' => true]);
    }

    /**
     * Get formatted phone number.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        return $this->phone ? preg_replace('/^(\+?[\d]{1,3})?[\s\-]?(\(?[\d]{3}\)?)[\s\-]?([\d]{3})[\s\-]?([\d]{4})$/', '$1 ($2) $3-$4', $this->phone) : null;
    }

    /**
     * Get formatted mobile number.
     */
    public function getFormattedMobileAttribute(): ?string
    {
        return $this->mobile ? preg_replace('/^(\+?[\d]{1,3})?[\s\-]?(\(?[\d]{3}\)?)[\s\-]?([\d]{3})[\s\-]?([\d]{4})$/', '$1 ($2) $3-$4', $this->mobile) : null;
    }
}
