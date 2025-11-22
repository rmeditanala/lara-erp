<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company (or create one if none exists)
        $company = Company::first();

        if (!$company) {
            $this->command->info('No company found. Please create a company first.');
            return;
        }

        $companyUser = User::where('company_id', $company->id)->first();

        if (!$companyUser) {
            $this->command->info('No users found for this company.');
            return;
        }

        // Create sample customers
        $customers = [
            [
                'name' => 'Tech Solutions Inc.',
                'email' => 'contact@techsolutions.com',
                'phone' => '+1 (555) 123-4567',
                'website' => 'https://techsolutions.com',
                'address' => '123 Tech Street',
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'United States',
                'postal_code' => '94102',
                'industry' => 'Technology',
                'employees_count' => 150,
                'annual_revenue' => 5000000.00,
                'status' => 'active',
                'source' => 'website',
                'notes' => 'Key account - high potential for recurring business.',
            ],
            [
                'name' => 'Global Manufacturing Co.',
                'email' => 'sales@globalmfg.com',
                'phone' => '+1 (555) 234-5678',
                'website' => 'https://globalmfg.com',
                'address' => '456 Industrial Ave',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'United States',
                'postal_code' => '60601',
                'industry' => 'Manufacturing',
                'employees_count' => 500,
                'annual_revenue' => 25000000.00,
                'status' => 'active',
                'source' => 'referral',
                'notes' => 'Referred by existing client - very interested in our premium services.',
            ],
            [
                'name' => 'Startup Innovations',
                'email' => 'hello@startupinnov.com',
                'phone' => '+1 (555) 345-6789',
                'website' => 'https://startupinnov.com',
                'address' => '789 Innovation Drive',
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'United States',
                'postal_code' => '73301',
                'industry' => 'Software',
                'employees_count' => 25,
                'annual_revenue' => 750000.00,
                'status' => 'prospect',
                'source' => 'cold_call',
                'notes' => 'Growing startup - potential for long-term partnership.',
            ],
            [
                'name' => 'Retail Masters LLC',
                'email' => 'procurement@retailmasters.com',
                'phone' => '+1 (555) 456-7890',
                'website' => 'https://retailmasters.com',
                'address' => '321 Commerce Blvd',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'United States',
                'postal_code' => '10001',
                'industry' => 'Retail',
                'employees_count' => 200,
                'annual_revenue' => 10000000.00,
                'status' => 'lead',
                'source' => 'trade_show',
                'notes' => 'Met at industry trade show - requested quote for large order.',
            ],
            [
                'name' => 'Healthcare Plus',
                'email' => 'admin@healthcareplus.com',
                'phone' => '+1 (555) 567-8901',
                'website' => 'https://healthcareplus.com',
                'address' => '654 Medical Center Way',
                'city' => 'Boston',
                'state' => 'MA',
                'country' => 'United States',
                'postal_code' => '02101',
                'industry' => 'Healthcare',
                'employees_count' => 75,
                'annual_revenue' => 3000000.00,
                'status' => 'active',
                'source' => 'referral',
                'notes' => 'Healthcare provider with compliance requirements.',
            ],
        ];

        $createdCustomers = [];
        foreach ($customers as $customerData) {
            $customer = Customer::create(array_merge($customerData, [
                'company_id' => $company->id,
                'created_by' => $companyUser->id,
            ]));
            $createdCustomers[] = $customer;
        }

        // Create sample quotes
        $quotes = [
            [
                'quote_number' => 'Q-2024-001',
                'subject' => 'Enterprise Software License',
                'subtotal' => 45000.00,
                'tax_rate' => 8.5,
                'total' => 48825.00,
                'status' => 'accepted',
                'valid_until' => now()->addDays(30),
                'notes' => 'Annual enterprise license with premium support.',
            ],
            [
                'quote_number' => 'Q-2024-002',
                'subject' => 'Manufacturing Equipment Package',
                'subtotal' => 125000.00,
                'tax_rate' => 7.0,
                'total' => 133750.00,
                'status' => 'sent',
                'valid_until' => now()->addDays(15),
                'notes' => 'Complete equipment package with installation.',
            ],
            [
                'quote_number' => 'Q-2024-003',
                'subject' => 'Startup Development Services',
                'subtotal' => 15000.00,
                'tax_rate' => 8.5,
                'total' => 16275.00,
                'status' => 'draft',
                'valid_until' => now()->addDays(30),
                'notes' => 'Custom development package for startup.',
            ],
            [
                'quote_number' => 'Q-2024-004',
                'subject' => 'Retail POS System',
                'subtotal' => 25000.00,
                'tax_rate' => 8.0,
                'total' => 27000.00,
                'status' => 'sent',
                'valid_until' => now()->addDays(7),
                'notes' => 'POS system with inventory management.',
            ],
            [
                'quote_number' => 'Q-2024-005',
                'subject' => 'Healthcare Software Suite',
                'subtotal' => 35000.00,
                'tax_rate' => 6.0,
                'total' => 37100.00,
                'status' => 'accepted',
                'valid_until' => now()->addDays(45),
                'notes' => 'HIPAA compliant software suite.',
            ],
        ];

        foreach ($quotes as $index => $quoteData) {
            // Create quotes over the last few months for realistic data
            $createdAt = now()->subMonths(rand(1, 5))->subDays(rand(1, 25));

            Quote::create(array_merge($quoteData, [
                'company_id' => $company->id,
                'customer_id' => $createdCustomers[$index % count($createdCustomers)]->id,
                'created_by' => $companyUser->id,
                'tax_amount' => $quoteData['subtotal'] * ($quoteData['tax_rate'] / 100),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]));
        }

        // Create some historical customers spread over months
        for ($i = 1; $i <= 15; $i++) {
            $createdAt = now()->subMonths(rand(0, 5))->subDays(rand(1, 28));

            Customer::create([
                'company_id' => $company->id,
                'created_by' => $companyUser->id,
                'name' => 'Customer ' . Str::random(8),
                'email' => 'customer' . $i . '@example.com',
                'phone' => '+1 (555) ' . str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT),
                'status' => ['lead', 'prospect', 'active'][rand(0, 2)],
                'source' => ['website', 'referral', 'cold_call', 'trade_show'][rand(0, 3)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Sample data created successfully!');
        $this->command->info('- Created ' . count($createdCustomers) . ' sample customers');
        $this->command->info('- Created ' . count($quotes) . ' sample quotes');
        $this->command->info('- Created 15 historical customers for dashboard data');
    }
}
