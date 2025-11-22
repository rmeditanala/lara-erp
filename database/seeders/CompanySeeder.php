<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample company
        $company = Company::create([
            'name' => 'Demo Corporation',
            'email' => 'info@demo-corp.com',
            'phone' => '+1 (555) 000-0000',
            'address' => '123 Demo Street',
            'city' => 'San Francisco',
            'country' => 'United States',
            'subscription_status' => 'active',
            'trial_ends_at' => now()->subDays(15),
            'subscription_ends_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Create a sample user for this company
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'job_title' => 'Administrator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Assign company owner role
        $user->assignRole('company-owner');

        $this->command->info('Sample company created successfully!');
        $this->command->info('Company: ' . $company->name);
        $this->command->info('User: demo@example.com / password');
    }
}