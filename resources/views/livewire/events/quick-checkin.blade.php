@section('title', $event['name'] . ' - Check-in')

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <div class="w-full max-w-md sm:max-w-lg lg:max-w-2xl mx-auto">
        <div class="mb-6 sm:mb-8 p-4 bg-white rounded-lg shadow-md flex items-center gap-4">
            <img src="{{ \App\Utils\Helper::generateURLImagePath($event['image_represent_path']) }}"
                alt="{{ $event['name'] }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg object-cover">

            <div class="flex flex-col">
                <h3 class="text-lg sm:text-xl font-bold text-gray-800">{{ $event['name'] }}</h3>
                <p class="text-sm text-gray-600">{{ $organizer['name'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-4 sm:p-6 lg:p-8">
            <div class="flex space-y-3 sm:space-y-0 flex-row justify-between items-center mb-6 lg:mb-8">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 text-center sm:text-left">
                    {{ $lang === 'en' ? 'Event Check-in' : 'Check-in Sự Kiện' }}
                </h2>
                <button wire:click="toggleLang"
                    class="px-3 py-2 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r cursor-pointer from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 self-center sm:self-auto text-sm font-medium">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
                            </path>
                        </svg>
                        {{ strtoupper($lang) }}
                    </span>
                </button>
            </div>

            @if ($resultStatus)
            <div class="mt-4 p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-xl font-bold text-green-800">{{ $resultTitle }}</h3>
                        <p class="text-sm text-green-700 mt-1">{{ $resultMessage }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-green-200">
                    <p class="text-sm font-medium text-gray-700 mb-2">
                        {{ $lang === 'en' ? 'Ticket Information:' : 'Thông tin vé:' }}
                    </p>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-800">
                            <span class="font-semibold">{{ $lang === 'en' ? 'Ticket Code:' : 'Mã vé:' }}</span>
                            <span class="ml-2 font-mono bg-gray-100 px-2 py-1 rounded">{{ $ticketCode }}</span>
                        </p>
                        <p class="text-sm text-gray-800">
                            <span class="font-semibold">{{ $lang === 'en' ? 'Seat:' : 'Ghế:' }}</span>
                            <span class="ml-2 font-mono bg-gray-100 px-2 py-1 rounded">{{ $seatName }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @elseif ($resultMessage)
            <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">{{ $resultTitle }}</h3>
                        <p class="text-sm text-red-700 mt-1">{{ $resultMessage }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if (!$resultStatus)
            <form wire:submit.prevent="checkin" class="space-y-4 sm:space-y-5 lg:space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ $lang === 'en' ? 'Email Address' : 'Địa chỉ Email' }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                </path>
                            </svg>
                        </div>
                        <input type="email" wire:model.blur="email"
                            placeholder="{{ $lang === 'en' ? 'Enter your email' : 'Nhập email của bạn' }}"
                            class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 @error('email') border-red-500 ring-red-200 @enderror">
                    </div>
                    @error('email')
                    <p class="text-xs sm:text-sm text-red-600 flex items-start mt-1">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ $lang === 'en' ? 'Phone Number' : 'Số điện thoại' }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                        </div>
                        <input type="text" wire:model.blur="phone"
                            placeholder="{{ $lang === 'en' ? 'Enter your phone number' : 'Nhập số điện thoại' }}"
                            class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 @error('phone') border-red-500 ring-red-200 @enderror">
                    </div>
                    @error('phone')
                    <p class="text-xs sm:text-sm text-red-600 flex items-start mt-1">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                    @enderror
                </div>

                <div class="pt-4 sm:pt-6">
                    <button type="submit" wire:loading.attr="disabled" wire:target="checkin"
                        class="w-full flex justify-center cursor-pointer items-center py-3 sm:py-4 px-4 sm:px-6 border border-transparent rounded-lg shadow-lg text-sm sm:text-base font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                        <span wire:loading.remove wire:target="checkin">
                            {{ $lang === 'en' ? 'Check-in' : 'Check-in' }}
                        </span>
                        <span wire:loading wire:target="checkin" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ $lang === 'en' ? 'Processing...' : 'Đang xử lý...' }}
                        </span>
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>