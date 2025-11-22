<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CompanyController extends Controller
{
    /**
     * Show the company registration form.
     */
    public function create()
    {
        return inertia('Company/Register');
    }

    /**
     * Store a newly created company and user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Company Information
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['required', 'email', 'max:255', 'unique:companies,email'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'company_city' => ['nullable', 'string', 'max:255'],
            'company_country' => ['nullable', 'string', 'max:255'],
            'company_domain' => ['nullable', 'string', 'max:255', 'unique:companies,domain'],

            // User Information (Company Owner)
            'user_name' => ['required', 'string', 'max:255'],
            'user_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_phone' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],

            // Terms
            'terms_accepted' => ['required', 'boolean', 'accepted'],
        ]);

        try {
            DB::beginTransaction();

            // Create the company
            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'],
                'address' => $validated['company_address'],
                'city' => $validated['company_city'],
                'country' => $validated['company_country'],
                'domain' => $validated['company_domain'],
                'subscription_status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'is_active' => true,
                'settings' => [
                    'timezone' => config('app.timezone'),
                    'currency' => 'USD',
                    'date_format' => 'Y-m-d',
                    'time_format' => '12h',
                ],
            ]);

            // Create the company owner user
            $user = User::create([
                'name' => $validated['user_name'],
                'email' => $validated['user_email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $company->id,
                'phone' => $validated['user_phone'],
                'job_title' => $validated['job_title'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Assign company owner role
            $user->assignRole('company-owner');

            // Log the user in
            auth()->login($user);

            DB::commit();

            return redirect()
                ->route('dashboard')
                ->with('success', 'Welcome! Your company has been registered successfully. You have a 30-day free trial.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }

    /**
     * Show the company dashboard after registration.
     */
    public function dashboard()
    {
        return inertia('Dashboard');
    }

    /**
     * Show company settings.
     */
    public function settings()
    {
        $company = auth()->user()->company;

        return inertia('Company/Settings', [
            'company' => $company,
        ]);
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Request $request)
    {
        $company = auth()->user()->company;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:companies,email,' . $company->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:companies,domain,' . $company->id],
            'logo_url' => ['nullable', 'url'],
            'settings.timezone' => ['nullable', 'string'],
            'settings.currency' => ['nullable', 'string', 'max:3'],
            'settings.date_format' => ['nullable', 'string'],
            'settings.time_format' => ['nullable', 'string', 'in:12h,24h'],
        ]);

        $company->update($validated);

        return back()
            ->with('success', 'Company settings updated successfully.');
    }

    /**
     * Show company subscription status.
     */
    public function subscription()
    {
        $company = auth()->user()->company;

        return inertia('Company/Subscription', [
            'company' => $company,
            'subscriptionPlans' => $this->getSubscriptionPlans(),
        ]);
    }

    /**
     * Get available subscription plans.
     */
    private function getSubscriptionPlans()
    {
        return [
            [
                'id' => 'starter',
                'name' => 'Starter',
                'price' => 29,
                'features' => [
                    'Up to 5 users',
                    'Basic CRM',
                    'Inventory management',
                    'Basic reporting',
                    'Email support',
                ],
            ],
            [
                'id' => 'professional',
                'name' => 'Professional',
                'price' => 79,
                'features' => [
                    'Up to 20 users',
                    'Advanced CRM',
                    'Full inventory',
                    'Advanced reporting',
                    'Project management',
                    'Priority email support',
                ],
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise',
                'price' => 199,
                'features' => [
                    'Unlimited users',
                    'All features',
                    'Custom integrations',
                    'Advanced analytics',
                    'Dedicated support',
                    'SLA guarantee',
                ],
            ],
        ];
    }
}
