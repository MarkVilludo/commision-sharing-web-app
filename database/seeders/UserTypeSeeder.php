<?php

namespace Database\Seeders;

use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Relative weights for splitting monthly profit (example reference sums to {@see ProfitPool::REFERENCE_WEIGHT_SUM}).
     * The divisor at runtime is the sum of weights for all commission recipients, not a fixed constant.
     *
     * @var list<array{name: string, percentage: float}>
     */
    private const TYPES = [
        ['name' => 'Administrator', 'percentage' => 0],
        ['name' => 'MSP Staffing agency - Owner (Facility)', 'percentage' => 0.5],
        ['name' => 'Msp Staffing agency - Clinician', 'percentage' => 7.5],
        ['name' => 'Factoring / Investor', 'percentage' => 3.0],
        ['name' => 'Accountant Dept', 'percentage' => 0.5],
        ['name' => 'Operator (O) Dept', 'percentage' => 0.5],
        ['name' => 'Sales (S) Dept', 'percentage' => 0.5],
        ['name' => 'Msp / HR Dept', 'percentage' => 0.5],
        ['name' => 'IT Dept', 'percentage' => 0.5],
        ['name' => 'Insurance / Worker Comp', 'percentage' => 1.5],
    ];

    public function run(): void
    {
        foreach (self::TYPES as $row) {
            UserType::query()->updateOrCreate(
                ['name' => $row['name']],
                ['percentage' => $row['percentage']]
            );
        }

        $poolSum = (float) UserType::query()->where('name', '!=', 'Administrator')->sum('percentage');
        if ($poolSum <= 0) {
            throw new \RuntimeException(
                'User types (excluding Administrator) must have a positive sum of weight columns; got '.$poolSum.'.'
            );
        }
    }

    /**
     * Display order for admin UI (matches reference sheet order).
     *
     * @return list<string>
     */
    public static function displayOrder(): array
    {
        return array_column(self::TYPES, 'name');
    }
}
