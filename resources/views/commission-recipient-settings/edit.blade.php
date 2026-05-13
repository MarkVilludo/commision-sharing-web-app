<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Salary & commision share') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl space-y-6">
                    <p class="text-sm text-gray-600">
                        {{ __('You can update your salary budget here. Your role’s commision weight is set by an administrator and is shown for reference.') }}
                    </p>

                    @if ($user->userType)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                            <h3 class="text-sm font-medium text-gray-900">{{ __('Your role & commision weight') }}</h3>
                            <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-gray-500">{{ __('Role') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $user->userType->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">{{ __('commision weight (relative share)') }}</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ number_format((float) $user->userType->percentage, 3, '.', '') }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    @if (session('status') === 'commission-settings-updated')
                        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200"
                            role="status">
                            {{ __('Saved.') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('commission-settings.update') }}" class="space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="salary" :value="__('Salary (budget)')" />
                            <x-text-input id="salary" name="salary" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('salary', $user->salary)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('salary')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
