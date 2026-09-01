<?php

namespace Database\Seeders;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\PlanPrice;
use App\Domain\TenantAdmin\Models\CentralUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Central Admin Staff
        CentralUser::firstOrCreate(
            ['email' => 'admin@tenantforge.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        CentralUser::firstOrCreate(
            ['email' => 'support@tenantforge.com'],
            [
                'name' => 'Support Agent',
                'password' => Hash::make('password'),
                'role' => 'support',
            ]
        );

        // 2. Seed Default Plans & Multi-Currency Pricing
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'billing_period' => 'monthly',
                'features' => ['tasks', 'basic-analytics'],
                'prices' => [
                    ['currency' => 'USD', 'amount' => 2900, 'gateway' => 'stripe'],
                    ['currency' => 'EUR', 'amount' => 2900, 'gateway' => 'stripe'],
                    ['currency' => 'GBP', 'amount' => 2500, 'gateway' => 'stripe'],
                    ['currency' => 'BRL', 'amount' => 14900, 'gateway' => 'stripe'],
                    ['currency' => 'INR', 'amount' => 249900, 'gateway' => 'stripe'],
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'billing_period' => 'monthly',
                'features' => ['tasks', 'team-invites', 'basic-analytics'],
                'prices' => [
                    ['currency' => 'USD', 'amount' => 7900, 'gateway' => 'stripe'],
                    ['currency' => 'EUR', 'amount' => 7900, 'gateway' => 'stripe'],
                    ['currency' => 'GBP', 'amount' => 6500, 'gateway' => 'stripe'],
                    ['currency' => 'BRL', 'amount' => 39900, 'gateway' => 'stripe'],
                    ['currency' => 'INR', 'amount' => 649900, 'gateway' => 'stripe'],
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'billing_period' => 'monthly',
                'features' => ['tasks', 'team-invites', 'advanced-analytics', 'custom-branding'],
                'prices' => [
                    ['currency' => 'USD', 'amount' => 19900, 'gateway' => 'stripe'],
                    ['currency' => 'EUR', 'amount' => 19900, 'gateway' => 'stripe'],
                    ['currency' => 'GBP', 'amount' => 16500, 'gateway' => 'stripe'],
                    ['currency' => 'BRL', 'amount' => 99900, 'gateway' => 'stripe'],
                    ['currency' => 'INR', 'amount' => 1649900, 'gateway' => 'stripe'],
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $prices = $planData['prices'];
            unset($planData['prices']);

            $plan = Plan::firstOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );

            foreach ($prices as $priceData) {
                PlanPrice::firstOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'currency' => $priceData['currency'],
                        'gateway' => $priceData['gateway'],
                    ],
                    $priceData
                );
            }
        }

        // 3. Seed Languages
        $this->call(LanguageSeeder::class);

        // 4. Seed Demo Multi-Tenant Workspaces
        $this->call(TenantSeeder::class);
    }
}
