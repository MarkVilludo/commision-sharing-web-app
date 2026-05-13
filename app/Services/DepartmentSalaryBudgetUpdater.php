<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Applies a total salary budget per user type (department) by updating each recipient’s {@see User::$salary}.
 * Multiple recipients in one type share the budget proportionally to their previous salaries, or equally if all were zero.
 */
class DepartmentSalaryBudgetUpdater
{
    /**
     * @param  array<int|string, float|int|string|null>  $budgetByUserTypeId
     */
    public function apply(array $budgetByUserTypeId): void
    {
        DB::transaction(function () use ($budgetByUserTypeId) {
            foreach ($budgetByUserTypeId as $typeId => $raw) {
                $typeId = (int) $typeId;
                $budget = round((float) $raw, 2);
                if ($budget < 0) {
                    continue;
                }

                /** @var Collection<int, User> $recipients */
                $recipients = User::query()
                    ->commissionRecipients()
                    ->where('user_type_id', $typeId)
                    ->orderBy('id')
                    ->get();

                if ($recipients->isEmpty()) {
                    continue;
                }

                $this->distributeBudgetAcrossRecipients($recipients, $budget);
            }

            User::query()
                ->commissionRecipients()
                ->orderBy('id')
                ->get()
                ->each(function (User $user): void {
                    $user->remaining_to_pay = round(max(0, (float) $user->salary - (float) $user->commissions), 2);
                    $user->save();
                });
        });
    }

    /**
     * @param  Collection<int, User>  $recipients
     */
    private function distributeBudgetAcrossRecipients(Collection $recipients, float $budget): void
    {
        $users = $recipients->values();
        $m = $users->count();

        if ($m === 1) {
            $users[0]->salary = $budget;
            $users[0]->save();

            return;
        }

        $oldSum = (float) $users->sum(fn (User $u) => (float) $u->salary);
        $new = [];

        if ($oldSum > 0) {
            for ($i = 0; $i < $m - 1; $i++) {
                $new[$i] = round($budget * (float) $users[$i]->salary / $oldSum, 2);
            }
            $new[$m - 1] = round($budget - array_sum(array_slice($new, 0, $m - 1, true)), 2);
        } else {
            $share = round($budget / $m, 2);
            for ($i = 0; $i < $m - 1; $i++) {
                $new[$i] = $share;
            }
            $new[$m - 1] = round($budget - $share * ($m - 1), 2);
        }

        for ($i = 0; $i < $m; $i++) {
            $users[$i]->salary = $new[$i];
            $users[$i]->save();
        }
    }
}
