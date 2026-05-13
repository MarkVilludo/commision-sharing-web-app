<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserType;
use App\Services\CommissionCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * All seeded users. First row is the platform admin (is_admin: true); rest are commission recipients.
     * Salaries match spreadsheet “Budget” where given; department budgets sum to 1,068,000.
     *
     * @var list<array{name: string, email: string, salary: float, user_type_name: string, is_admin?: bool}>
     */
    private const USERS = [
        [
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'salary' => 0,
            'user_type_name' => 'Administrator',
            'is_admin' => true,
        ],
        ['name' => 'Jordan Lee', 'email' => 'owner@example.com', 'salary' => 0, 'user_type_name' => 'MSP Staffing agency - Owner (Facility)'],
        ['name' => 'Alex Morgan', 'email' => 'clinician@example.com', 'salary' => 0, 'user_type_name' => 'Msp Staffing agency - Clinician'],
        ['name' => 'Riley Chen', 'email' => 'investor@example.com', 'salary' => 0, 'user_type_name' => 'Factoring / Investor'],
        ['name' => 'Sam Patel', 'email' => 'accounting@example.com', 'salary' => 100_000.00, 'user_type_name' => 'Accountant Dept'],
        ['name' => 'Taylor Brooks', 'email' => 'operations@example.com', 'salary' => 100_000.00, 'user_type_name' => 'Operator (O) Dept'],
        ['name' => 'Casey Rivera', 'email' => 'sales@example.com', 'salary' => 433_000.00, 'user_type_name' => 'Sales (S) Dept'],
        ['name' => 'Morgan Diaz', 'email' => 'hr@example.com', 'salary' => 150_000.00, 'user_type_name' => 'Msp / HR Dept'],
        ['name' => 'Jamie Fox', 'email' => 'it@example.com', 'salary' => 285_000.00, 'user_type_name' => 'IT Dept'],
        ['name' => 'Drew Kim', 'email' => 'insurance@example.com', 'salary' => 0, 'user_type_name' => 'Insurance / Worker Comp'],
    ];

    public function run(): void
    {
        $calculator = new CommissionCalculator;
        /** Sample monthly profit for seeded commission/remaining fields (shares use pool of 15). */
        $sampleGrossTotal = 100_000_000.00;

        foreach (self::USERS as $row) {
            $type = UserType::query()->where('name', $row['user_type_name'])->firstOrFail();
            $isAdmin = (bool) ($row['is_admin'] ?? false);

            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'salary' => $row['salary'],
                    'user_type_id' => $type->id,
                    'commissions' => 0,
                    'remaining_to_pay' => 0,
                    'is_admin' => $isAdmin,
                ]
            );
        }

        $calculator->persistDistributionForRecipients($sampleGrossTotal);
    }
}
