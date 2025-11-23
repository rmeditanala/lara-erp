<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'company_name',
        'job_title',
        'website',
        'description',
        'status',
        'source',
        'industry',
        'employees',
        'estimated_value',
        'currency',
        'priority',
        'score',
        'rating',
        'follow_up_date',
        'notes',
        'converted_at',
        'converted_by',
        'lost_reason',
        'lost_details',
        'custom_fields',
        'tags',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'employees' => 'integer',
        'priority' => 'integer',
        'score' => 'integer',
        'follow_up_date' => 'date',
        'converted_at' => 'datetime',
        'custom_fields' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the company that owns the lead.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who owns the lead.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the customer this lead was converted to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who converted this lead.
     */
    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    /**
     * Get all activities for this lead.
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
     * Get the formatted estimated value.
     */
    public function getFormattedEstimatedValueAttribute(): string
    {
        if (!$this->estimated_value) {
            return 'N/A';
        }

        return number_format($this->estimated_value, 2) . ' ' . $this->currency;
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'gray',
            'contacted' => 'blue',
            'qualified' => 'green',
            'proposal' => 'yellow',
            'negotiation' => 'orange',
            'converted' => 'emerald',
            'lost' => 'red',
            'recycled' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get the rating color for UI.
     */
    public function getRatingColorAttribute(): string
    {
        return match ($this->rating) {
            'hot' => 'red',
            'warm' => 'yellow',
            'cold' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Check if lead is converted.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted' && $this->customer_id !== null;
    }

    /**
     * Check if lead is lost.
     */
    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    /**
     * Check if lead is active (not converted or lost).
     */
    public function isActive(): bool
    {
        return !$this->isConverted() && !$this->isLost();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Record activity when lead is created
        static::created(function ($lead) {
            Activity::createForSubject(
                $lead,
                'lead_created',
                "Lead Created: {$lead->full_name}",
                "New lead {$lead->full_name} from {$lead->company_name ?: 'individual'} was added",
                [
                    'lead_name' => $lead->full_name,
                    'company_name' => $lead->company_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'source' => $lead->source,
                ],
                auth()->user()
            );
        });

        // Record activity when lead status changes
        static::updated(function ($lead) {
            if ($lead->wasChanged('status')) {
                $oldStatus = $lead->getOriginal('status');
                $newStatus = $lead->status;

                Activity::createForSubject(
                    $lead,
                    'lead_status_changed',
                    "Lead Status Changed: {$lead->full_name}",
                    "Lead status changed from {$oldStatus} to {$newStatus}",
                    [
                        'lead_name' => $lead->full_name,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                    ],
                    auth()->user()
                );
            }

            // Record lead conversion
            if ($lead->wasChanged('customer_id') && $lead->customer_id !== null) {
                Activity::createForSubject(
                    $lead,
                    'lead_converted',
                    "Lead Converted: {$lead->full_name}",
                    "Lead {$lead->full_name} was converted to a customer",
                    [
                        'lead_name' => $lead->full_name,
                        'customer_id' => $lead->customer_id,
                    ],
                    auth()->user()
                );
            }
        });
    }

    /**
     * Scope leads by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope active leads (not converted or lost).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['converted', 'lost']);
    }

    /**
     * Scope converted leads.
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope lost leads.
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('status', 'lost');
    }

    /**
     * Scope leads by owner.
     */
    public function scopeByOwner(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope leads that need follow-up.
     */
    public function scopeNeedingFollowUp(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_date')
            ->where('follow_up_date', '<=', now())
            ->whereNotIn('status', ['converted', 'lost']);
    }

    /**
     * Scope high priority leads.
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', '>=', 4)
            ->active();
    }

    /**
     * Scope hot leads.
     */
    public function scopeHot(Builder $query): Builder
    {
        return $query->where('rating', 'hot')
            ->active();
    }

    /**
     * Scope leads by search query.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('job_title', 'like', "%{$search}%");
        });
    }

    /**
     * Convert lead to customer.
     */
    public function convertToCustomer(User $user): Customer
    {
        return DB::transaction(function () use ($user) {
            // Create customer from lead
            $customer = Customer::create([
                'company_id' => $this->company_id,
                'display_name' => $this->company_name ?: $this->full_name,
                'customer_type' => $this->company_name ? 'company' : 'individual',
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'website' => $this->website,
                'industry' => $this->industry,
                'description' => $this->description,
                'is_active' => true,
            ]);

            // Create primary contact if lead has contact info
            if ($this->email || $this->phone) {
                CustomerContact::create([
                    'customer_id' => $customer->id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'mobile' => $this->mobile,
                    'job_title' => $this->job_title,
                    'is_primary' => true,
                    'is_active' => true,
                    'notes' => $this->notes,
                ]);
            }

            // Update lead
            $this->update([
                'status' => 'converted',
                'customer_id' => $customer->id,
                'converted_at' => now(),
                'converted_by' => $user->id,
            ]);

            return $customer;
        });
    }

    /**
     * Update lead score based on various factors.
     */
    public function updateScore(): void
    {
        $score = 0;

        // Email presence
        if ($this->email) $score += 10;

        // Phone presence
        if ($this->phone) $score += 10;

        // Company presence
        if ($this->company_name) $score += 15;

        // Job title presence
        if ($this->job_title) $score += 5;

        // Website presence
        if ($this->website) $score += 5;

        // Estimated value
        if ($this->estimated_value) {
            if ($this->estimated_value >= 100000) $score += 25;
            elseif ($this->estimated_value >= 50000) $score += 20;
            elseif ($this->estimated_value >= 10000) $score += 15;
            elseif ($this->estimated_value >= 1000) $score += 10;
        }

        // Priority level
        $score += ($this->priority - 1) * 5;

        // Industry (B2B generally higher value)
        $highValueIndustries = ['technology', 'finance', 'healthcare', 'manufacturing'];
        if ($this->industry && in_array(strtolower($this->industry), $highValueIndustries)) {
            $score += 15;
        }

        // Employees size
        if ($this->employees) {
            if ($this->employees >= 1000) $score += 20;
            elseif ($this->employees >= 100) $score += 15;
            elseif ($this->employees >= 50) $score += 10;
            elseif ($this->employees >= 10) $score += 5;
        }

        // Update rating based on score
        $this->score = $score;
        if ($score >= 70) {
            $this->rating = 'hot';
        } elseif ($score >= 40) {
            $this->rating = 'warm';
        } elseif ($score >= 20) {
            $this->rating = 'cold';
        }

        $this->saveQuietly();
    }
}
