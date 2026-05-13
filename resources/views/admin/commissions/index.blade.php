<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Commission distribution') }}
            </h2>
            <a href="{{ route('admin.commission-months.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                {{ __('Monthly reports') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200" role="status">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <p class="text-sm text-gray-600">
                        {{ __('Choose the report month and enter monthly profit to distribute. Each user type has a weight; each recipient’s commission is profit × (their weight ÷ sum of all recipients’ weights). Saved commissions always add up to that profit. Example weights in settings sum to :reference.', ['reference' => \App\Services\ProfitPool::REFERENCE_WEIGHT_SUM]) }}
                    </p>

                    <form method="POST" action="{{ route('admin.commissions.store') }}" class="flex flex-col lg:flex-row lg:flex-wrap lg:items-end gap-4">
                        @csrf
                        <div class="flex-1 min-w-[12rem]">
                            <x-input-label for="report_month" :value="__('Report month')" />
                            <x-text-input
                                id="report_month"
                                name="report_month"
                                type="month"
                                class="mt-1 block w-full"
                                :value="$reportMonth"
                                required
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('report_month')" />
                        </div>
                        <div class="flex-1 min-w-[12rem]">
                            <x-input-label for="gross_total" :value="__('Monthly profit to distribute')" />
                            <x-text-input
                                id="gross_total"
                                name="gross_total"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                :value="$grossTotal"
                                required
                                autofocus
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('gross_total')" />
                        </div>
                        <div>
                            <x-primary-button type="submit">
                                {{ __('Calculate & save') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('By user type') }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Totals are summed across all recipients in each type.') }}</p>

                    @if (! $typeSummaries->isEmpty())
                        <div class="mb-4 flex flex-wrap gap-x-8 gap-y-2 rounded-md bg-gray-50 px-4 py-3 text-sm border border-gray-200">
                            <div>
                                <span class="text-gray-600">{{ __('Total commission (all types)') }}</span>
                                <span class="ms-1 font-semibold tabular-nums text-gray-900">{{ number_format($grandTotals['commission'], 2) }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($typeSummaries->isEmpty())
                        <p class="text-sm text-gray-600">{{ __('No user types found.') }}</p>
                    @else
                        <div class="overflow-x-auto -mx-6 sm:mx-0">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('User type') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Percentage amount of profit') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Total salary (budget)') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Total remaining to pay') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($typeSummaries as $row)
                                        @php
                                            $type = $row['user_type'];
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-900 font-medium max-w-md">
                                                {{ $type->name }}
                                            </td>
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format($row['commission_total'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format($row['salary_total'], 2) }}
                                            </td>
                                          
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format($row['remaining_total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                                    <tr>
                                        <th scope="row" class="px-4 py-3 text-left font-semibold text-gray-900">{{ __('Overall totals') }}</th>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">
                                            {{ number_format($grandTotals['commission'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">
                                            {{ number_format($grandTotals['salary'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">
                                            {{ number_format($grandTotals['remaining'], 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
