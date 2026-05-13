<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use App\Services\CommissionCalculator;
use App\Services\CommissionMonthReportRecorder;
use App\Services\DepartmentSalaryBudgetUpdater;
use App\Services\ProfitPool;
use App\Services\UserTypeCommissionTotals;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionDistributionController extends Controller
{
    public function index(Request $request): View
    {
        $typeSummaries = UserTypeCommissionTotals::summarize();
        $grandTotals = UserTypeCommissionTotals::grandTotals($typeSummaries);
        $totalRecipientWeight = ProfitPool::totalRecipientWeight();

        $grossTotal = old(
            'gross_total',
            session('last_gross_total')
        );

        $reportMonth = old(
            'report_month',
            session('last_report_month', now()->format('Y-m'))
        );

        return view('admin.commissions.index', [
            'typeSummaries' => $typeSummaries,
            'grandTotals' => $grandTotals,
            'grossTotal' => $grossTotal,
            'reportMonth' => $reportMonth,
            'totalRecipientWeight' => $totalRecipientWeight,
        ]);
    }

    public function store(
        Request $request,
        CommissionCalculator $calculator,
        CommissionMonthReportRecorder $monthRecorder
    ): RedirectResponse {
        $validated = $request->validate([
            'gross_total' => ['required', 'numeric', 'min:0'],
            'report_month' => ['required', 'date_format:Y-m'],
        ]);

        $gross = (float) $validated['gross_total'];
        $reportMonth = $validated['report_month'];

        $count = $calculator->persistDistributionForRecipients($gross);
        $monthRecorder->record($reportMonth, $gross);

        return redirect()
            ->route('admin.commissions.index')
            ->with('success', __('Commission amounts updated for :count users. Monthly report saved for :month.', [
                'count' => $count,
                'month' => Carbon::createFromFormat('Y-m', $reportMonth)->translatedFormat('F Y'),
            ]))
            ->with('last_gross_total', $gross)
            ->with('last_report_month', $reportMonth);
    }

    public function updateSalaries(Request $request, DepartmentSalaryBudgetUpdater $salaryUpdater): RedirectResponse
    {
        $typeIds = UserType::query()
            ->whereHas('recipientUsers')
            ->pluck('id')
            ->all();

        if ($typeIds === []) {
            return redirect()
                ->route('admin.commissions.index')
                ->with('success', __('Department salary budgets updated.'));
        }

        $rules = [
            'salary_budget' => ['required', 'array'],
        ];
        foreach ($typeIds as $id) {
            $rules['salary_budget.'.$id] = ['required', 'numeric', 'min:0'];
        }

        $validated = $request->validate($rules);

        /** @var array<int|string, float|int|string> $budgets */
        $budgets = $validated['salary_budget'];
        $filtered = [];
        foreach ($typeIds as $id) {
            $value = $budgets[$id] ?? $budgets[(string) $id] ?? null;
            if ($value !== null) {
                $filtered[(int) $id] = $value;
            }
        }

        $salaryUpdater->apply($filtered);

        return redirect()
            ->route('admin.commissions.index')
            ->with('success', __('Department salary budgets updated.'));
    }
}
