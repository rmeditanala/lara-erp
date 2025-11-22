<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the executive dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            abort(403, 'You must belong to a company to view the dashboard.');
        }

        // KPI Metrics
        $kpiData = $this->getKPIData($company);

        // Recent Activity
        $recentActivity = $this->getRecentActivity($company);

        // Sales Chart Data
        $salesChartData = $this->getSalesChartData($company);

        // Customer Growth Data
        $customerGrowthData = $this->getCustomerGrowthData($company);

        return Inertia::render('ExecutiveDashboard', [
            'kpiData' => $kpiData,
            'recentActivity' => $recentActivity,
            'salesChartData' => $salesChartData,
            'customerGrowthData' => $customerGrowthData,
        ]);
    }

    /**
     * Get KPI metrics for the company.
     */
    private function getKPIData($company): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Customer metrics
        $totalCustomers = $company->customers()->count();
        $newCustomersThisMonth = $company->customers()->where('created_at', '>=', $currentMonth)->count();
        $newCustomersLastMonth = $company->customers()->where('created_at', '>=', $lastMonth)->where('created_at', '<=', $lastMonthEnd)->count();
        $customerGrowth = $newCustomersLastMonth > 0 ? (($newCustomersThisMonth - $newCustomersLastMonth) / $newCustomersLastMonth) * 100 : 0;

        // Quote metrics
        $totalQuotes = $company->quotes()->count();
        $activeQuotes = $company->quotes()->whereIn('status', ['draft', 'sent'])->count();
        $quotesThisMonth = $company->quotes()->where('created_at', '>=', $currentMonth)->count();
        $quotesLastMonth = $company->quotes()->where('created_at', '>=', $lastMonth)->where('created_at', '<=', $lastMonthEnd)->count();
        $quoteGrowth = $quotesLastMonth > 0 ? (($quotesThisMonth - $quotesLastMonth) / $quotesLastMonth) * 100 : 0;

        // Revenue metrics (from accepted quotes)
        $totalRevenue = $company->quotes()->where('status', 'accepted')->sum('total');
        $revenueThisMonth = $company->quotes()->where('status', 'accepted')->where('created_at', '>=', $currentMonth)->sum('total');
        $revenueLastMonth = $company->quotes()->where('status', 'accepted')->where('created_at', '>=', $lastMonth)->where('created_at', '<=', $lastMonthEnd)->sum('total');
        $revenueGrowth = $revenueLastMonth > 0 ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 : 0;

        // User metrics
        $totalUsers = $company->users()->count();
        $activeUsers = $company->users()->where('is_active', true)->count();

        return [
            'totalCustomers' => $totalCustomers,
            'customerGrowth' => round($customerGrowth, 1),
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'activeQuotes' => $activeQuotes,
            'quoteGrowth' => round($quoteGrowth, 1),
            'totalRevenue' => $totalRevenue,
            'revenueGrowth' => round($revenueGrowth, 1),
            'revenueThisMonth' => $revenueThisMonth,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
        ];
    }

    /**
     * Get recent activity for the company.
     */
    private function getRecentActivity($company): array
    {
        $activities = [];

        // Recent customers
        $recentCustomers = $company->customers()
            ->latest()
            ->take(3)
            ->get(['id', 'name', 'email', 'status', 'created_at']);

        foreach ($recentCustomers as $customer) {
            $activities[] = [
                'id' => $customer->id,
                'type' => 'customer',
                'title' => 'New Customer Added',
                'description' => "{$customer->name} ({$customer->email})",
                'status' => $customer->status,
                'created_at' => $customer->created_at->toISOString(),
                'icon' => 'Users',
                'color' => 'blue',
            ];
        }

        // Recent quotes
        $recentQuotes = $company->quotes()
            ->with('customer:name')
            ->latest()
            ->take(3)
            ->get(['id', 'quote_number', 'customer_id', 'subject', 'total', 'status', 'created_at']);

        foreach ($recentQuotes as $quote) {
            $activities[] = [
                'id' => $quote->id,
                'type' => 'quote',
                'title' => "Quote #{$quote->quote_number}",
                'description' => $quote->subject . ' - $' . number_format($quote->total, 2),
                'customer' => $quote->customer->name,
                'status' => $quote->status,
                'created_at' => $quote->created_at->toISOString(),
                'icon' => 'ShoppingCart',
                'color' => 'green',
            ];
        }

        // Recent user invitations
        $recentInvitations = $company->userInvitations()
            ->with('inviter:name')
            ->latest()
            ->take(2)
            ->get(['id', 'email', 'role', 'invited_by', 'created_at']);

        foreach ($recentInvitations as $invitation) {
            $activities[] = [
                'id' => $invitation->id,
                'type' => 'invitation',
                'title' => 'User Invitation Sent',
                'description' => "{$invitation->email} as {$this->getRoleDisplayName($invitation->role)}",
                'inviter' => $invitation->inviter->name,
                'status' => 'pending',
                'created_at' => $invitation->created_at->toISOString(),
                'icon' => 'Mail',
                'color' => 'orange',
            ];
        }

        // Sort by created_at and limit to 10 items
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get sales chart data for the last 6 months.
     */
    private function getSalesChartData($company): array
    {
        $months = [];
        $revenue = [];
        $quotes = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $months[] = $month->format('M Y');

            $monthRevenue = $company->quotes()
                ->where('status', 'accepted')
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<=', $monthEnd)
                ->sum('total');

            $monthQuotes = $company->quotes()
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<=', $monthEnd)
                ->count();

            $revenue[] = $monthRevenue;
            $quotes[] = $monthQuotes;
        }

        return [
            'months' => $months,
            'revenue' => $revenue,
            'quotes' => $quotes,
        ];
    }

    /**
     * Get customer growth data for the last 6 months.
     */
    private function getCustomerGrowthData($company): array
    {
        $months = [];
        $customers = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $months[] = $month->format('M Y');

            $monthCustomers = $company->customers()
                ->where('created_at', '>=', $monthStart)
                ->where('created_at', '<=', $monthEnd)
                ->count();

            $customers[] = $monthCustomers;
        }

        return [
            'months' => $months,
            'customers' => $customers,
        ];
    }

    /**
     * Get display name for role.
     */
    private function getRoleDisplayName(string $role): string
    {
        $roleNames = [
            'company-owner' => 'Company Owner',
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'sales-rep' => 'Sales Representative',
            'employee' => 'Employee',
            'read-only' => 'Read Only'
        ];
        return $roleNames[$role] ?? $role;
    }
}
