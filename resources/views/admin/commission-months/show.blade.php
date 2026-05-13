<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Report for :month', ['month' => $report->report_month->translatedFormat('F Y')]) }}
            </h2>
            <div class="flex gap-4 text-sm">
                <a href="{{ route('admin.commission-months.index') }}" class="text-indigo-600 hover:text-indigo-800">
                    {{ __('← All months') }}
                </a>
                <a href="{{ route('admin.commissions.index') }}" class="text-indigo-600 hover:text-indigo-800">
                    {{ __('Calculator') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-sm text-gray-600 space-y-1">
                    <p><span class="font-medium text-gray-700">{{ __('Monthly profit distributed:') }}</span> {{ number_format((float) $report->gross_total, 2) }}</p>
                    <p><span class="font-medium text-gray-700">{{ __('Total salary (budget, all types):') }}</span> {{ number_format($grandSalary, 2) }}</p>
                    <p><span class="font-medium text-gray-700">{{ __('Total commission (all types):') }}</span> {{ number_format((float) $report->total_commission, 2) }}</p>
                    <p class="text-xs text-gray-500">{{ __('Stored snapshot per user type at save time.') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('By user type') }}</h3>
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
                                @foreach ($report->lines as $line)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-900 font-medium max-w-md">{{ $line->user_type_name }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format((float) $line->total_commission, 2) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format((float) $line->total_salary, 2) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format((float) $line->total_remaining, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                                <tr>
                                    <th scope="row" class="px-4 py-3 text-left font-semibold text-gray-900">{{ __('Overall totals') }}</th>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($grandCommission, 2) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($grandSalary, 2) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold text-gray-900">{{ number_format($grandRemaining, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
