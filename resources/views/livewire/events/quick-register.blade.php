@section('title', $event['name'] . ' - ' . $organizer['name'])

@section('styles')


    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('registration-success', () => {
                console.log('Registration successful!');
            });

            Livewire.on('registration-error', (event) => {
                console.error('Registration error:', event.message);
            });
        });
    </script>
@endsection

<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
    <div class="w-full max-w-md sm:max-w-lg lg:max-w-2xl mx-auto">
        <div class="mb-6 sm:mb-8 p-4 bg-gray-100 rounded-lg flex items-center gap-4">
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
                    {{ __('auth.register.heading') }}
                </h2>
                <button wire:click="toggleLang()"
                    class="px-3 py-2 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r cursor-pointer from-indigo-500 to-purple-600 text-white hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 self-center sm:self-auto text-sm font-medium">
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

            {{-- Hiển thị thông báo lỗi chung (nếu có) --}}
            @if (session()->has('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Hiển thị thông tin thành công và vé khi $resultStatus là TRUE --}}
            @if ($resultStatus)
                <div class="space-y-6">
                    {{-- Thông báo thành công --}}
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg leading-6 font-medium text-green-800">
                                    {{ $successTitle ?: ($lang === 'en' ? 'Registration Successful!' : 'Đăng Ký Thành Công!') }}
                                </h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p>{{ $successMessage }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Khu vực thông tin Vé --}}
                    @if ($ticketCode || $seatName)
                        <div class="p-5 border border-indigo-200 bg-indigo-50 rounded-xl shadow-md">
                            <h4 class="text-lg font-bold text-indigo-700 mb-3 border-b pb-2 border-indigo-200">
                                {{ $lang === 'en' ? 'Your Ticket Details' : 'Thông Tin Vé Của Bạn' }}
                            </h4>
                            <dl class="space-y-3 text-sm text-gray-700">
                                @if ($ticketCode)
                                    <div class="flex justify-between items-center">
                                        <dt class="font-medium text-indigo-800">
                                            {{ $lang === 'en' ? 'Ticket Code' : 'Mã Vé' }}:
                                        </dt>
                                        <dd
                                            class="font-extrabold text-indigo-900 text-lg tracking-wider bg-indigo-200 px-3 py-1 rounded-full">
                                            {{ $ticketCode }}
                                        </dd>
                                    </div>
                                @endif
                                @if ($seatName)
                                    <div class="flex justify-between items-center">
                                        <dt class="font-medium text-indigo-800">
                                            {{ $lang === 'en' ? 'Seat/Area' : 'Vị Trí/Khu Vực' }}:
                                        </dt>
                                        <dd class="font-semibold text-indigo-900 text-base">{{ $seatName }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    @if(!$isUserExist)
                    <div class="pt-4 w-full border-t border-gray-200">
                        <p class="text-sm text-gray-700 mb-2 font-semibold">
                            {{ $lang === 'en' ? 'Your temporary login details:' : 'Thông tin đăng nhập tạm thời:' }}
                        </p>
                        <div class="text-left bg-gray-100 p-3 rounded-lg border border-gray-200">
                            <p class="text-sm font-medium text-gray-900">
                                Email: <span class="font-bold">{{ $email }}</span>
                            </p>
                            <p class="text-sm font-medium text-gray-900 mt-1">
                                {{ $lang === 'en' ? 'Password: Your phone number.' : 'Mật khẩu: Số điện thoại của bạn' }}
                            </p>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $lang === 'en' ? 'Organizer' : 'Tên tổ chức' }}: <span
                                    class="font-bold">{{ $organizer['name'] }}</span>
                            </p>
                        </div>
                    </div>
                    @else
                        {{-- Thông báo không có thông tin đăng nhập --}}
                        <div class="pt-4 w-full border-t border-gray-200">
                            <p class="text-sm text-gray-700 mb-2 font-semibold">
                                {{ $lang === 'en' ? 'You already have an account with us.' : 'Bạn đã có tài khoản với chúng tôi.' }}
                            </p>
                            <p class="text-sm text-gray-700">
                                {{ $lang === 'en' ? 'Please use your existing account to log in.' : 'Vui lòng đăng nhập để trải nghiệm sự kiện.' }}
                            </p>
                        </div>
                    @endif

                    {{-- Nút tiếp tục --}}
                    <a href="#"
                        class="w-full flex justify-center items-center py-3 sm:py-4 px-4 sm:px-6 border border-transparent rounded-lg shadow-lg text-sm sm:text-base font-semibold text-white bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                        {{ $lang === 'en' ? 'Continue to Event Page' : 'Tiếp tục đến Trang Sự Kiện' }}
                    </a>
                </div>
            @else
                {{-- Form đăng ký --}}
                <form wire:submit.prevent="register" class="space-y-4 sm:space-y-5 lg:space-y-6">
                    {{-- Name Field --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $lang === 'en' ? 'Full Name' : 'Họ và tên' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            {{-- Sử dụng wire:model.blur để validate khi blur (rời khỏi field) --}}
                            <input type="text" wire:model.blur="name"
                                placeholder="{{ $lang === 'en' ? 'Enter your full name' : 'Nhập họ và tên của bạn' }}"
                                class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 @error('name') border-red-500 ring-2 ring-red-200 @enderror">

                            {{-- Loading indicator cho validation --}}
                            <div wire:loading wire:target="name"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        @error('name')
                            <p class="text-xs sm:text-sm text-red-600 flex items-start mt-1 animate-fade-in">
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

                    {{-- Email Field --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $lang === 'en' ? 'Email Address' : 'Địa chỉ Email' }}
                            <span class="text-red-500">*</span>
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
                                class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 @error('email') border-red-500 ring-2 ring-red-200 @enderror">

                            <div wire:loading wire:target="email"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        @error('email')
                            <p class="text-xs sm:text-sm text-red-600 flex items-start mt-1 animate-fade-in">
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

                    {{-- Phone Field --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $lang === 'en' ? 'Phone Number' : 'Số điện thoại' }}
                            <span class="text-red-500">*</span>
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
                                class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 placeholder-gray-400 @error('phone') border-red-500 ring-2 ring-red-200 @enderror">

                            <div wire:loading wire:target="phone"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        @error('phone')
                            <p class="text-xs sm:text-sm text-red-600 flex items-start mt-1 animate-fade-in">
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

                    {{-- Submit Button --}}
                    <div class="pt-4 sm:pt-6">
                        <button type="submit" wire:loading.attr="disabled" wire:target="register"
                            class="w-full flex justify-center cursor-pointer items-center py-3 sm:py-4 px-4 sm:px-6 border border-transparent rounded-lg shadow-lg text-sm sm:text-base font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <span wire:loading.remove wire:target="register">
                                {{ $lang === 'en' ? 'Register' : 'Đăng ký' }}
                            </span>
                            <span wire:loading wire:target="register" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                {{ $lang === 'en' ? 'Creating...' : 'Đang tạo...' }}
                            </span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
