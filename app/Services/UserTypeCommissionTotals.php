<?php

namespace App\Services;

use App\Models\UserType;
use Database\Seeders\UserTypeSeeder;
use Illuminate\Support\Collection;

final class UserTypeCommissionTotals
{
    /**
     * @return Collection<int, array{
     *     user_type: UserType,
     *     recipient_count: int,
     *     salary_total: float,
     *     commission_total: float,
     *     remaining_total: float
     * }>
     */
    public static function summarize(): Collection
    {
        $order = UserTypeSeeder::displayOrder();
        $orderIndex = array_flip($order);

        return UserType::query()
            ->with('recipientUsers')
            ->get()
            ->sortBy(fn (UserType $type) => $orderIndex[$type->name] ?? 999)
            ->values()
            ->map(function (UserType $type) {
                $recipients = $type->recipientUsers;
                $salaryTotal = (float) $recipients->sum(fn ($u) => (float) $u->salary);
                $commissionTotal = (float) $recipients->sum(fn ($u) => (float) $u->commissions);
                $remainingTotal = round(max(0, $salaryTotal - $commissionTotal), 2);

                return [
                    'user_type' => $type,
                    'recipient_count' => $recipients->count(),
                    'salary_total' => $salaryTotal,
                    'commission_total' => $commissionTotal,
                    'remaining_total' => $remainingTotal,
                ];
            });
    }

    /**
     * @param  Collection<int, array{salary_total: float, commission_total: float, remaining_total: float}>  $summaries
     * @return array{salary: float, commission: float, remaining: float}
     */
    public static function grandTotals(Collection $summaries): array
    {
        return [
            'salary' => (float) $summaries->sum('salary_total'),
            'commission' => (float) $summaries->sum('commission_total'),
            'remaining' => (float) $summaries->sum('remaining_total'),
        ];
    }
}
