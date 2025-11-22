<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'subject_type',
        'subject_id',
        'type',
        'title',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the company that owns the activity.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject of the activity (polymorphic relationship).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new activity record.
     */
    public static function createForSubject(
        Model $subject,
        string $type,
        string $title,
        ?string $description = null,
        ?array $metadata = null,
        ?User $user = null
    ): self {
        return static::create([
            'company_id' => $subject->company_id ?? $user?->company_id,
            'user_id' => $user?->id,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope activities by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope activities by subject.
     */
    public function scopeForSubject($query, Model $subject)
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id);
    }

    /**
     * Scope activities by company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope recent activities.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted metadata for display.
     */
    public function getFormattedMetadataAttribute(): string
    {
        if (empty($this->metadata)) {
            return '';
        }

        $formatted = [];
        foreach ($this->metadata as $key => $value) {
            $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }

        return implode("\n", $formatted);
    }

    /**
     * Get activity icon based on type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'created' => 'user-plus',
            'updated' => 'edit',
            'deleted' => 'trash-2',
            'status_changed' => 'toggle-left',
            'primary_set' => 'star',
            'primary_removed' => 'star-off',
            'contact_added' => 'user-plus',
            'contact_updated' => 'edit',
            'contact_deactivated' => 'user-x',
            'contact_activated' => 'user-check',
            default => 'activity',
        };
    }

    /**
     * Get activity color based on type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            'created', 'contact_added', 'contact_activated' => 'green',
            'updated', 'contact_updated' => 'blue',
            'deleted', 'contact_deactivated' => 'red',
            'status_changed' => 'yellow',
            'primary_set' => 'amber',
            'primary_removed' => 'gray',
            default => 'gray',
        };
    }
}
