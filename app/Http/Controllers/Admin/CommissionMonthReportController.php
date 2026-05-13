<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionMonthReport;
use Illuminate\View\View;

class CommissionMonthReportController extends Controller
{
    public function index(): View
    {
        $reports = CommissionMonthReport::query()
            ->withSum('lines', 'total_salary')
            ->orderByDesc('report_month')
            ->get();

        $listTotals = [
            'profit' => (float) $reports->sum(fn (CommissionMonthReport $r) => (float) $r->gross_total),
            'salary' => (float) $reports->sum(fn (CommissionMonthReport $r) => (float) ($r->lines_sum_total_salary ?? 0)),
            'commission' => (float) $reports->sum(fn (CommissionMonthReport $r) => (float) $r->total_commission),
            'remaining' => (float) $reports->sum(fn (CommissionMonthReport $r) => (float) $r->total_remaining),
        ];

        return view('admin.commission-months.index', [
            'reports' => $reports,
            'listTotals' => $listTotals,
        ]);
    }

    public function show(CommissionMonthReport $report): View
    {
        $report->load('lines');

        $grandSalary = (float) $report->lines->sum(fn ($l) => (float) $l->total_salary);
        $grandCommission = (float) $report->lines->sum(fn ($l) => (float) $l->total_commission);
        $grandRemaining = (float) $report->lines->sum(fn ($l) => (float) $l->total_remaining);

        return view('admin.commission-months.show', [
            'report' => $report,
            'grandSalary' => $grandSalary,
            'grandCommission' => $grandCommission,
            'grandRemaining' => $grandRemaining,
        ]);
    }
}
