<div class="flex gap-2 justify-center">
    <button wire:click="switchLanguage('vi')" type="button" @class([
        'flex items-center text-gray-700 dark:text-gray-300 gap-2 px-3 py-2 rounded-lg transition-all duration-200 border shadow-sm',

        app()->getLocale() != 'vi'
            ? 'bg-primary-600 text-white dark:bg-primary-500 shadow-md scale-105 border-transparent'
            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-primary-300',
    ]) title="{{ __('Tiếng Việt') }}">
        <img src="{{ asset('images/Vietnam.svg.png') }}" alt="Vietnam flag" class="w-6 h-4 rounded-sm object-cover" />
        <span
            class="font-medium text-sm {{ app()->getLocale() != 'vi' ? 'text-white dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">VI</span>
    </button>

    <button wire:click="switchLanguage('en')" type="button" @class([
        'flex items-center gap-2 text-gray-700 dark:text-gray-300 px-3 py-2 rounded-lg transition-all duration-200 border shadow-sm',

        app()->getLocale() != 'en'
            ? 'bg-primary-600 text-white dark:bg-primary-500 shadow-md scale-105 border-transparent'
            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-primary-300',
    ]) title="{{ __('English') }}">
        <img src="{{ asset('images/Flag_of_the_United_Kingdom.svg.png') }}" alt="UK flag"
            class="w-6 h-4 rounded-sm object-cover" />
        <span
            class="font-medium text-sm {{ app()->getLocale() != 'en' ? 'text-white dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">EN</span>
    </button>
</div>
