<x-filament::page>
    <div class="space-y-6">
        <div class="p-6 bg-white rounded-lg shadow dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white">{{ __('admin.payos_guide.title') }}</h2>

            <ol class="relative border-l pr-2 border-gray-200 dark:border-gray-700 ml-3">
                <li class="mb-10 ml-6">
                    <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.payos_guide.step_1') }}</h3>
                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">{!! __('admin.payos_guide.step_1_desc') !!}</p>
                </li>
                <li class="mb-10 ml-6">
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.payos_guide.step_2') }}</h3>
                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">{{ __('admin.payos_guide.step_2_desc') }}</p>
                </li>
                <li class="mb-10 ml-6">
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.payos_guide.step_3') }}</h3>
                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">{!! __('admin.payos_guide.step_3_desc') !!}</p>
                </li>
                <li class="mb-10 ml-6">
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.payos_guide.step_4') }}</h3>
                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">{{ __('admin.payos_guide.step_4_desc') }}</p>
                    <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between">
                            <code class="text-sm text-gray-900 dark:text-white break-all" id="webhook-url">{{ url('/api/webhook/payos') }}</code>
                            <button
                                x-data="{}"
                                x-on:click="window.navigator.clipboard.writeText('{{ url('/api/webhook/payos') }}'); $tooltip('Copied!', { timeout: 1500 });"
                                class="ml-2 p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                                title="Copy URL">
                                <x-heroicon-o-clipboard-document class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </li>
                <li class="ml-6">
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ __('admin.payos_guide.step_5') }}</h3>
                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">{!! __('admin.payos_guide.step_5_desc') !!}</p>
                </li>
            </ol>
        </div>
    </div>
</x-filament::page>
