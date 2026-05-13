<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommissionRecipientSettingsUpdateRequest;
use App\Services\ProfitPool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CommissionRecipientSettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('userType');

        $totalRecipientWeight = ProfitPool::totalRecipientWeight();
        $roleWeight = $user->userType !== null ? (float) $user->userType->percentage : null;
        $plannedPercentOfProfit = ($roleWeight !== null && $totalRecipientWeight > 0)
            ? ProfitPool::percentFromWeights($roleWeight, $totalRecipientWeight)
            : null;

        return view('commission-recipient-settings.edit', [
            'user' => $user,
            'plannedPercentOfProfit' => $plannedPercentOfProfit,
        ]);
    }

    public function update(CommissionRecipientSettingsUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->salary = $validated['salary'];
        $user->remaining_to_pay = round(
            max(0, (float) $user->salary - (float) $user->commissions),
            2
        );
        $user->save();

        return Redirect::route('commission-settings.edit')->with('status', 'commission-settings-updated');
    }
}
