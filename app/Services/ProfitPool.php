<?php

namespace App\Services;

use App\Models\User;

/**
 * Monthly profit is split across commission recipients in proportion to each user’s
 * {@see UserType::$percentage} weight. The divisor is the sum of those weights (dynamic), not a fixed 15.
 *
 * Example reference weights (0.5+7.5+3+…) sum to 15 only as a template; any positive weights work.
 */
final class ProfitPool
{
    /**
     * Reference example: sum of weights in the staffing spreadsheet (for documentation only).
     */
    public const REFERENCE_WEIGHT_SUM = 15.0;

    public static function totalRecipientWeight(): float
    {
        return (float) User::query()
            ->commissionRecipients()
            ->with('userType')
            ->get()
            ->sum(fn (User $u) => (float) ($u->userType?->percentage ?? 0));
    }

    /**
     * One user’s share before rounding; caller handles cent reconciliation.
     */
    public static function allocationRaw(float $monthlyProfit, float $weight, float $totalWeight): float
    {
        if ($totalWeight <= 0 || $monthlyProfit <= 0) {
            return 0.0;
        }

        return $monthlyProfit * ($weight / $totalWeight);
    }

    public static function percentOfEnteredProfit(float $commissionPortion, float $monthlyProfit): float
    {
        if ($monthlyProfit <= 0) {
            return 0.0;
        }

        return round($commissionPortion / $monthlyProfit * 100, 4);
    }

    /**
     * Planned % of profit from weights alone (when no saved profit is shown yet).
     */
    public static function percentFromWeights(float $weightSumForLine, float $totalWeight): float
    {
        if ($totalWeight <= 0) {
            return 0.0;
        }

        return round($weightSumForLine / $totalWeight * 100, 4);
    }
}
