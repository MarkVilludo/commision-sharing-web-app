<?php

namespace App\Services;

use App\Models\CommissionMonthReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommissionMonthReportRecorder
{
    /**
     * Replace any existing snapshot for the calendar month and store per–user-type lines.
     */
    public function record(string $yearMonth, float $grossTotal): CommissionMonthReport
    {
        $month = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();

        return DB::transaction(function () use ($month, $grossTotal) {
            $summaries = UserTypeCommissionTotals::summarize();
            $grandCommission = (float) $summaries->sum('commission_total');
            $grandRemaining = (float) $summaries->sum('remaining_total');

            // Use upsert + explicit fetch: updateOrCreate can miss rows when report_month is date-cast,
            // causing INSERT and UNIQUE (report_month) failures on SQLite/MySQL.
            $monthStart = $month->copy()->startOfMonth()->toDateString();
            $now = now();

            CommissionMonthReport::query()->upsert(
                [[
                    'report_month' => $monthStart,
                    'gross_total' => $grossTotal,
                    'total_commission' => $grandCommission,
                    'total_remaining' => $grandRemaining,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]],
                ['report_month'],
                ['gross_total', 'total_commission', 'total_remaining', 'updated_at']
            );

            $report = CommissionMonthReport::query()
                ->whereRaw('report_month = ?', [$monthStart])
                ->firstOrFail();

            $report->lines()->delete();

            $sort = 0;
            foreach ($summaries as $row) {
                $type = $row['user_type'];
                $report->lines()->create([
                    'user_type_id' => $type->id,
                    'user_type_name' => $type->name,
                    'percentage' => $type->percentage,
                    'recipient_count' => $row['recipient_count'],
                    'total_salary' => $row['salary_total'],
                    'total_commission' => $row['commission_total'],
                    'total_remaining' => $row['remaining_total'],
                    'sort_order' => $sort++,
                ]);
            }

            return $report->load('lines');
        });
    }
}
