<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'lead_id',
        'title',
        'description',
        'account_name',
        'pipeline',
        'stage',
        'stage_order',
        'amount',
        'currency',
        'probability',
        'weighted_amount',
        'expected_close_days',
        'status',
        'priority',
        'type',
        'source',
        'contact_name',
        'contact_email',
        'contact_phone',
        'decision_maker',
        'decision_makers',
        'competitors',
        'competitive_advantages',
        'competitive_weaknesses',
        'expected_close_date',
        'next_steps',
        'follow_up_date',
        'team_members',
        'created_by',
        'won_by',
        'lost_by',
        'won_reason',
        'lost_reason',
        'actual_amount',
        'closed_date',
        'sales_cycle_days',
        'custom_fields',
        'tags',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'probability' => 'decimal:2',
        'weighted_amount' => 'decimal:2',
        'expected_close_date' => 'date',
        'follow_up_date' => 'date',
        'closed_date' => 'date',
        'decision_makers' => 'array',
        'team_members' => 'array',
        'custom_fields' => 'array',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The default pipeline stages with their order and default probabilities
     */
    public const DEFAULT_PIPELINE_STAGES = [
        'prospecting' => ['order' => 1, 'default_probability' => 10],
        'qualification' => ['order' => 2, 'default_probability' => 25],
        'needs_analysis' => ['order' => 3, 'default_probability' => 40],
        'value_proposition' => ['order' => 4, 'default_probability' => 50],
        'proposal' => ['order' => 5, 'default_probability' => 75],
        'negotiation' => ['order' => 6, 'default_probability' => 85],
        'closed_won' => ['order' => 7, 'default_probability' => 100],
        'closed_lost' => ['order' => 8, 'default_probability' => 0],
    ];

    /**
     * Pipeline types available in the system
     */
    public const PIPELINE_TYPES = [
        'sales' => 'Sales Pipeline',
        'customer_success' => 'Customer Success Pipeline',
        'renewal' => 'Renewal Pipeline',
        'partnership' => 'Partnership Pipeline',
    ];

    /**
     * Opportunity statuses
     */
    public const STATUSES = [
        'active' => 'Active',
        'won' => 'Won',
        'lost' => 'Lost',
        'paused' => 'Paused',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Priority levels
     */
    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ];

    /**
     * Opportunity types
     */
    public const TYPES = [
        'new_business' => 'New Business',
        'existing_business' => 'Existing Business',
        'renewal' => 'Renewal',
        'expansion' => 'Expansion',
        'upsell' => 'Upsell',
        'cross_sell' => 'Cross-sell',
    ];

    /**
     * Opportunity sources
     */
    public const SOURCES = [
        'inbound' => 'Inbound',
        'outbound' => 'Outbound',
        'referral' => 'Referral',
        'website' => 'Website',
        'social_media' => 'Social Media',
        'email_campaign' => 'Email Campaign',
        'cold_call' => 'Cold Call',
        'trade_show' => 'Trade Show',
        'partner' => 'Partner',
        'other' => 'Other',
    ];

    /**
     * Boot the model and apply event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Calculate weighted amount when amount or probability changes
        static::saving(function ($opportunity) {
            if ($opportunity->amount && $opportunity->probability) {
                $opportunity->weighted_amount = ($opportunity->amount * $opportunity->probability) / 100;
            } else {
                $opportunity->weighted_amount = null;
            }

            // Set stage order based on stage if not provided
            if (!$opportunity->stage_order && $opportunity->stage) {
                $opportunity->stage_order = self::getStageOrder($opportunity->stage);
            }

            // Calculate sales cycle days when closing deal
            if ($opportunity->closed_date && !$opportunity->sales_cycle_days) {
                $opportunity->sales_cycle_days = $opportunity->created_at->diffInDays($opportunity->closed_date);
            }

            // Auto-set follow-up date if expected close date is set and no follow-up date
            if ($opportunity->expected_close_date && !$opportunity->follow_up_date) {
                $opportunity->follow_up_date = $opportunity->expected_close_date->subDays(7);
            }
        });

        // Record activity when opportunity status changes
        static::updated(function ($opportunity) {
            if ($opportunity->wasChanged(['status', 'stage', 'user_id', 'amount'])) {
                Activity::create([
                    'company_id' => $opportunity->company_id,
                    'user_id' => auth()->id(),
                    'subject_type' => static::class,
                    'subject_id' => $opportunity->id,
                    'action' => 'updated',
                    'description' => $opportunity->getActivityDescription(),
                    'properties' => $opportunity->getActivityProperties(),
                ]);
            }
        });
    }

    /**
     * Get the company that owns the opportunity.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who owns the opportunity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lead associated with the opportunity.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who created the opportunity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who won the opportunity.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'won_by');
    }

    /**
     * Get the user who lost the opportunity.
     */
    public function loser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lost_by');
    }

    /**
     * Get the activities for the opportunity.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->where('subject_type', static::class);
    }

    /**
     * Scope a query to only include opportunities for a specific pipeline.
     */
    public function scopeForPipeline($query, string $pipeline)
    {
        return $query->where('pipeline', $pipeline);
    }

    /**
     * Scope a query to only include opportunities in a specific stage.
     */
    public function scopeInStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope a query to only include active opportunities.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include won opportunities.
     */
    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    /**
     * Scope a query to only include lost opportunities.
     */
    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    /**
     * Scope a query to only include opportunities with a specific priority.
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include opportunities closing within specified days.
     */
    public function scopeClosingInDays($query, int $days)
    {
        return $query->whereDate('expected_close_date', '<=', now()->addDays($days))
            ->where('expected_close_date', '>=', now());
    }

    /**
     * Scope a query to only include overdue opportunities.
     */
    public function scopeOverdue($query)
    {
        return $query->whereDate('expected_close_date', '<', now())
            ->where('status', 'active');
    }

    /**
     * Scope a query to include opportunities assigned to the user or their team.
     */
    public function scopeAssignedToUserOrTeam($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereJsonContains('team_members', ['user_id' => $userId]);
        });
    }

    /**
     * Get opportunities grouped by pipeline stage for kanban view.
     */
    public static function getKanbanData(string $pipeline = 'sales', ?int $userId = null)
    {
        $query = static::forPipeline($pipeline)->with(['user', 'lead']);

        if ($userId) {
            $query->assignedToUserOrTeam($userId);
        }

        $opportunities = $query->get();

        $stages = self::getPipelineStages($pipeline);
        $kanban = [];

        foreach ($stages as $stage => $data) {
            $kanban[$stage] = [
                'name' => $stage,
                'label' => ucwords(str_replace('_', ' ', $stage)),
                'order' => $data['order'],
                'opportunities' => $opportunities->filter(fn($opp) => $opp->stage === $stage),
                'total_value' => $opportunities->filter(fn($opp) => $opp->stage === $stage)->sum('amount'),
                'weighted_value' => $opportunities->filter(fn($opp) => $opp->stage === $stage)->sum('weighted_amount'),
            ];
        }

        return $kanban;
    }

    /**
     * Get pipeline stages configuration.
     */
    public static function getPipelineStages(string $pipeline = 'sales'): array
    {
        // In a real application, this could be customizable per pipeline
        return self::DEFAULT_PIPELINE_STAGES;
    }

    /**
     * Get stage order by stage name.
     */
    public static function getStageOrder(string $stage): int
    {
        $stages = self::getPipelineStages();
        return $stages[$stage]['order'] ?? 999;
    }

    /**
     * Calculate win probability based on multiple factors.
     */
    public function calculateProbability(): float
    {
        if ($this->status === 'won') {
            return 100;
        }

        if ($this->status === 'lost' || $this->status === 'cancelled') {
            return 0;
        }

        $baseProbability = self::getPipelineStages()[$this->stage]['default_probability'] ?? 25;

        // Adjust probability based on various factors
        $adjustments = 0;

        // Positive factors
        if ($this->amount && $this->amount > 100000) $adjustments += 5; // High value deals
        if ($this->contact_email && $this->contact_phone) $adjustments += 5; // Complete contact info
        if ($this->decision_maker) $adjustments += 10; // Has decision maker
        if ($this->team_members && count($this->team_members) > 1) $adjustments += 5; // Team collaboration
        if ($this->follow_up_date && $this->follow_up_date->isFuture()) $adjustments += 3;

        // Negative factors
        if (!$this->amount) $adjustments -= 10; // No amount specified
        if (!$this->contact_email) $adjustments -= 5; // No email
        if ($this->competitors) $adjustments -= 10; // Has competition
        if ($this->expected_close_date && $this->expected_close_date->isPast()) $adjustments -= 15;

        return max(0, min(100, $baseProbability + $adjustments));
    }

    /**
     * Update probability based on current conditions.
     */
    public function updateProbability(): void
    {
        $this->probability = $this->calculateProbability();
        $this->save();
    }

    /**
     * Move opportunity to a different stage.
     */
    public function moveToStage(string $newStage, ?int $userId = null): void
    {
        $oldStage = $this->stage;

        $this->stage = $newStage;
        $this->stage_order = self::getStageOrder($newStage);
        $this->user_id = $userId ?? $this->user_id;

        // Auto-update status based on stage
        if (in_array($newStage, ['closed_won'])) {
            $this->status = 'won';
            $this->won_by = auth()->id();
            $this->closed_date = now();
        } elseif (in_array($newStage, ['closed_lost'])) {
            $this->status = 'lost';
            $this->lost_by = auth()->id();
            $this->closed_date = now();
        } elseif ($this->status === 'won' || $this->status === 'lost') {
            // Moving out of closed stages resets status
            $this->status = 'active';
        }

        $this->updateProbability();
        $this->save();

        // Record stage change activity
        Activity::create([
            'company_id' => $this->company_id,
            'user_id' => auth()->id(),
            'subject_type' => static::class,
            'subject_id' => $this->id,
            'action' => 'stage_changed',
            'description' => "Moved from '{$oldStage}' to '{$newStage}'",
            'properties' => [
                'old_stage' => $oldStage,
                'new_stage' => $newStage,
            ],
        ]);
    }

    /**
     * Mark opportunity as won.
     */
    public function markAsWon(float $actualAmount = null, string $reason = null): void
    {
        $this->status = 'won';
        $this->stage = 'closed_won';
        $this->stage_order = self::getStageOrder('closed_won');
        $this->won_by = auth()->id();
        $this->closed_date = now();
        $this->probability = 100;

        if ($actualAmount) {
            $this->actual_amount = $actualAmount;
        }

        if ($reason) {
            $this->won_reason = $reason;
        }

        $this->save();

        // Convert lead to customer if associated
        if ($this->lead && $this->lead->status === 'converted') {
            Activity::create([
                'company_id' => $this->company_id,
                'user_id' => auth()->id(),
                'subject_type' => static::class,
                'subject_id' => $this->id,
                'action' => 'won',
                'description' => "Opportunity won for {$this->title}",
                'properties' => [
                    'actual_amount' => $this->actual_amount,
                    'won_reason' => $this->won_reason,
                ],
            ]);
        }
    }

    /**
     * Mark opportunity as lost.
     */
    public function markAsLost(string $reason = null): void
    {
        $this->status = 'lost';
        $this->stage = 'closed_lost';
        $this->stage_order = self::getStageOrder('closed_lost');
        $this->lost_by = auth()->id();
        $this->closed_date = now();
        $this->probability = 0;

        if ($reason) {
            $this->lost_reason = $reason;
        }

        $this->save();

        Activity::create([
            'company_id' => $this->company_id,
            'user_id' => auth()->id(),
            'subject_type' => static::class,
            'subject_id' => $this->id,
            'action' => 'lost',
            'description' => "Opportunity lost for {$this->title}",
            'properties' => [
                'lost_reason' => $this->lost_reason,
            ],
        ]);
    }

    /**
     * Get pipeline statistics for a company.
     */
    public static function getPipelineStats(int $companyId, string $pipeline = 'sales', ?int $userId = null): array
    {
        $query = static::where('company_id', $companyId)->forPipeline($pipeline);

        if ($userId) {
            $query->assignedToUserOrTeam($userId);
        }

        $opportunities = $query->get();

        return [
            'total_opportunities' => $opportunities->count(),
            'total_value' => $opportunities->sum('amount'),
            'weighted_value' => $opportunities->sum('weighted_amount'),
            'won_count' => $opportunities->where('status', 'won')->count(),
            'won_value' => $opportunities->where('status', 'won')->sum('actual_amount'),
            'lost_count' => $opportunities->where('status', 'lost')->count(),
            'active_count' => $opportunities->where('status', 'active')->count(),
            'average_deal_size' => $opportunities->where('status', 'won')->avg('actual_amount'),
            'win_rate' => $opportunities->whereIn('status', ['won', 'lost'])->count() > 0
                ? ($opportunities->where('status', 'won')->count() / $opportunities->whereIn('status', ['won', 'lost'])->count()) * 100
                : 0,
            'average_sales_cycle' => $opportunities->whereNotNull('sales_cycle_days')->avg('sales_cycle_days'),
            'stages' => self::getKanbanData($pipeline, $userId),
        ];
    }

    /**
     * Get activity description for model changes.
     */
    protected function getActivityDescription(): string
    {
        $descriptions = [];

        if ($this->wasChanged('status')) {
            $descriptions[] = "Status changed to {$this->status}";
        }

        if ($this->wasChanged('stage')) {
            $descriptions[] = "Stage changed to {$this->stage}";
        }

        if ($this->wasChanged('amount')) {
            $descriptions[] = "Amount updated to {$this->amount}";
        }

        if ($this->wasChanged('user_id')) {
            $descriptions[] = "Assigned to new user";
        }

        return implode(', ', $descriptions) ?: 'Opportunity updated';
    }

    /**
     * Get activity properties for tracking changes.
     */
    protected function getActivityProperties(): array
    {
        return [
            'title' => $this->title,
            'amount' => $this->amount,
            'stage' => $this->stage,
            'status' => $this->status,
            'probability' => $this->probability,
            'user_id' => $this->user_id,
        ];
    }

    /**
     * Get formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute(): string
    {
        if (!$this->amount) return '';

        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        return $symbol . number_format($this->amount, 2);
    }

    /**
     * Get probability as percentage with color indicator.
     */
    public function getProbabilityStatusAttribute(): string
    {
        if ($this->probability >= 75) return 'high';
        if ($this->probability >= 50) return 'medium';
        if ($this->probability >= 25) return 'low';
        return 'very-low';
    }

    /**
     * Check if opportunity is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_close_date && $this->expected_close_date->isPast() && $this->status === 'active';
    }

    /**
     * Get days until expected close.
     */
    public function getDaysToCloseAttribute(): ?int
    {
        if (!$this->expected_close_date) return null;

        return now()->diffInDays($this->expected_close_date, false);
    }

    /**
     * Get days in current stage.
     */
    public function getDaysInCurrentStageAttribute(): ?int
    {
        // This would require tracking stage changes - simplified version
        if (!$this->updated_at) return null;

        return now()->diffInDays($this->updated_at);
    }
}
