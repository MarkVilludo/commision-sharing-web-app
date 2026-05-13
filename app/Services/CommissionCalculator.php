<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Splits entered monthly profit across commission recipients so the rounded amounts
 * sum exactly to the profit (last recipient absorbs cent drift).
 */
class CommissionCalculator
{
    public function commissionAmount(User $user, float|string|int $monthlyProfit): float
    {
        $profit = (float) $monthlyProfit;
        $weight = (float) ($user->userType?->percentage ?? 0);
        $totalWeight = ProfitPool::totalRecipientWeight();

        return round(ProfitPool::allocationRaw($profit, $weight, $totalWeight), 2);
    }

    public function remainingToPay(User $user, float|string|int $monthlyProfit): float
    {
        $salary = (float) $user->salary;
        $commission = $this->commissionAmount($user, $monthlyProfit);

        return round(max(0, $salary - $commission), 2);
    }

    /**
     * @return array{commissions: float, remaining_to_pay: float}
     */
    public function applyToUser(User $user, float|string|int $monthlyProfit): array
    {
        $commissions = $this->commissionAmount($user, $monthlyProfit);
        $remaining = $this->remainingToPay($user, $monthlyProfit);

        $user->commissions = $commissions;
        $user->remaining_to_pay = $remaining;

        return [
            'commissions' => $commissions,
            'remaining_to_pay' => $remaining,
        ];
    }

    /**
     * Recalculate and persist commissions so Σ commissions equals {@see $monthlyProfit} (after cent fix).
     *
     * @return int Number of users updated
     */
    public function persistDistributionForRecipients(float|string|int $monthlyProfit): int
    {
        $profit = (float) $monthlyProfit;

        /** @var Collection<int, User> $users */
        $users = User::query()
            ->commissionRecipients()
            ->with('userType')
            ->orderBy('id')
            ->get();

        $totalWeight = $users->sum(fn (User $u) => (float) ($u->userType?->percentage ?? 0));

        if ($users->isEmpty()) {
            return 0;
        }

        if ($profit <= 0 || $totalWeight <= 0) {
            foreach ($users as $user) {
                $user->commissions = 0;
                $user->remaining_to_pay = round(max(0, (float) $user->salary), 2);
                $user->save();
            }

            return $users->count();
        }

        $rounded = [];
        foreach ($users as $i => $user) {
            $w = (float) ($user->userType?->percentage ?? 0);
            $rounded[$i] = round(ProfitPool::allocationRaw($profit, $w, $totalWeight), 2);
        }

        $drift = round($profit - array_sum($rounded), 2);
        if ($drift !== 0.0) {
        $lastIndex = $users->count() - 1;
        $rounded[$lastIndex] = round($rounded[$lastIndex] + $drift, 2);
        }

        foreach ($users as $i => $user) {
            $commission = $rounded[$i];
            $user->commissions = $commission;
            $user->remaining_to_pay = round(max(0, (float) $user->salary - $commission), 2);
            $user->save();
        }

        return $users->count();
    }
}
