<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'created_by',
        'customer_type',
        'name',
        'company_name',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'industry',
        'tax_number',
        'registration_number',
        'employees_count',
        'annual_revenue',
        'status',
        'source',
        'assigned_to',
        'notes',
        'is_active',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'annual_revenue' => 'decimal:2',
        'employees_count' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company that owns the customer.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the customer.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user assigned to the customer.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the contacts for the customer.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    /**
     * Get the deals for the customer.
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get the quotes for the customer.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Get the invoices for the customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all activities for the customer.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get the primary contact for the customer.
     */
    public function getPrimaryContactAttribute()
    {
        return $this->contacts()->where('is_primary', true)->first();
    }

    /**
     * Get the display name (company name or individual name).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->customer_type === 'company' ? ($this->company_name ?: $this->name) : $this->name;
    }

    /**
     * Get the total value of active deals.
     */
    public function getActiveDealsValueAttribute()
    {
        return $this->deals()
            ->where('status', 'active')
            ->sum('value');
    }

    /**
     * Get the total value of won deals.
     */
    public function getWonDealsValueAttribute()
    {
        return $this->deals()
            ->where('status', 'won')
            ->sum('value');
    }

    /**
     * Get formatted annual revenue.
     */
    public function getFormattedAnnualRevenueAttribute(): string
    {
        return $this->annual_revenue ? '$' . number_format($this->annual_revenue, 2) : 'N/A';
    }

    /**
     * Get formatted phone number.
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        return $this->phone ? preg_replace('/^(\+?[\d]{1,3})?[\s\-]?(\(?[\d]{3}\)?)[\s\-]?([\d]{3})[\s\-]?([\d]{4})$/', '$1 ($2) $3-$4', $this->phone) : null;
    }

    /**
     * Scope to only include active customers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only include customers of a specific type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('customer_type', $type);
    }

    /**
     * Scope to only include customers with a specific status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to search customers by name, email, or company name.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('company_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter customers by industry.
     */
    public function scopeInIndustry(Builder $query, string $industry): Builder
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope to filter customers by assigned user.
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to get customers created within a date range.
     */
    public function scopeCreatedBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include customers with a specific status (legacy).
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include active customers (legacy).
     */
    public function scopeActiveStatus($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if customer is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    /**
     * Get customers count by type for the current company.
     */
    public static function getCustomersCountByType()
    {
        return DB::table('customers')
            ->select('customer_type', DB::raw('count(*) as count'))
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->groupBy('customer_type')
            ->pluck('count', 'customer_type');
    }

    /**
     * Get customers count by status for the current company.
     */
    public static function getCustomersCountByStatus()
    {
        return DB::table('customers')
            ->select('status', DB::raw('count(*) as count'))
            ->where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    /**
     * Get top customers by revenue for the current company.
     */
    public static function getTopCustomersByRevenue($limit = 10)
    {
        return self::where('company_id', auth()->user()->company_id)
            ->whereNotNull('annual_revenue')
            ->where('annual_revenue', '>', 0)
            ->orderBy('annual_revenue', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'company_name', 'annual_revenue']);
    }

    /**
     * Get recent customers for the current company.
     */
    public static function getRecentCustomers($limit = 5)
    {
        return self::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->with(['assignedUser:id,name', 'contacts'])
            ->get();
    }

    /**
     * Get customer statistics for dashboard.
     */
    public static function getDashboardStats()
    {
        $companyId = auth()->user()->company_id;

        return [
            'total_customers' => self::where('company_id', $companyId)->where('is_active', true)->count(),
            'new_customers_this_month' => self::where('company_id', $companyId)
                ->where('is_active', true)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'active_customers' => self::where('company_id', $companyId)->where('status', 'active')->count(),
            'total_revenue' => self::where('company_id', $companyId)
                ->whereNotNull('annual_revenue')
                ->sum('annual_revenue'),
            'customers_by_type' => self::getCustomersCountByType(),
            'customers_by_status' => self::getCustomersCountByStatus(),
        ];
    }

    /**
     * Get list of available customer statuses.
     */
    public static function getStatuses(): array
    {
        return [
            'lead' => 'Lead',
            'prospect' => 'Prospect',
            'active' => 'Active Customer',
            'inactive' => 'Inactive',
            'churned' => 'Churned',
        ];
    }

    /**
     * Get list of available customer types.
     */
    public static function getTypes(): array
    {
        return [
            'individual' => 'Individual',
            'company' => 'Company',
        ];
    }

    /**
     * Get list of available industries.
     */
    public static function getIndustries(): array
    {
        return [
            'technology' => 'Technology',
            'healthcare' => 'Healthcare',
            'finance' => 'Finance & Banking',
            'manufacturing' => 'Manufacturing',
            'retail' => 'Retail',
            'construction' => 'Construction',
            'consulting' => 'Consulting',
            'education' => 'Education',
            'government' => 'Government',
            'non_profit' => 'Non-Profit',
            'other' => 'Other',
        ];
    }

    /**
     * Get list of available customer sources.
     */
    public static function getSources(): array
    {
        return [
            'website' => 'Website',
            'referral' => 'Referral',
            'cold_call' => 'Cold Call',
            'email' => 'Email Campaign',
            'social_media' => 'Social Media',
            'trade_show' => 'Trade Show',
            'partner' => 'Partner',
            'other' => 'Other',
        ];
    }
}
