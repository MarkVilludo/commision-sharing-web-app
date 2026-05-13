<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monthly commission reports') }}
            </h2>
            <a href="{{ route('admin.commissions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                {{ __('← Back to commission calculator') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($reports->isEmpty())
                        <p class="text-sm text-gray-600">{{ __('No monthly reports yet. Save a distribution from the commission page for a calendar month.') }}</p>
                    @else
                        <div class="overflow-x-auto -mx-6 sm:mx-0">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">{{ __('Month') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Monthly profit') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Total salary (budget)') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Total commission') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700">{{ __('Total remaining to pay') }}</th>
                                        <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($reports as $report)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-900">
                                                {{ $report->report_month->translatedFormat('F Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format((float) $report->gross_total, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format((float) ($report->lines_sum_total_salary ?? 0), 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format((float) $report->total_commission, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">
                                                {{ number_format((float) $report->total_remaining, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('admin.commission-months.show', $report) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                    {{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if ($reports->isNotEmpty())
                                    <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                                        <tr>
                                            <th scope="row" class="px-4 py-3 text-left font-semibold text-gray-900">{{ __('Overall totals (listed months)') }}</th>
                                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($listTotals['salary'], 2) }}</td>
                                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($listTotals['commission'], 2) }}</td>
                                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($listTotals['profit'], 2) }}</td>
                                            <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($listTotals['remaining'], 2) }}</td>
                                            <td class="px-4 py-3"></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
