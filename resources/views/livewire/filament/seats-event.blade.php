<div
    class="min-h-screen bg-gradient-to-br from-slate-100 to-indigo-200
    from-gray-100 via-indigo-100 to-purple-200 p-6">

    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $event->name }}</h1>
                <p class="text-gray-600 mt-2">Quản lý khu vực và ghế ngồi</p>
            </div>
            <button wire:click="$set('showAreaModal', true)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Thêm Khu Vực
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div wire:loading wire:target="updateArea, updateSeatName, selectArea, deleteArea "
            class="fixed inset-0 flex items-center justify-center z-50 bg-white/80 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                <p class="text-gray-700 font-semibold">Đang xử lý...</p>
            </div>
        </div>

        <div class="bg-gradient-to-r from-indigo-600 to-amber-500 text-white text-center py-6">

            <div class="flex items-center justify-center space-x-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v18a1 1 0 01-1 1H4a1 1 0 01-1-1V1a1 1 0 011-1h2a1 1 0 011 1v3" />
                </svg>
                <span class="text-xl font-bold">SÂN KHẤU / STAGE</span>
            </div>
        </div>

        <div class="p-8">
            @if (empty($areas))
                <div class="text-center py-16">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-500 mb-2">Chưa có khu vực nào</h3>
                    <p class="text-gray-400">Nhấn "Thêm Khu Vực" để bắt đầu tạo sơ đồ chỗ ngồi</p>
                </div>
            @else
                <div class="flex justify-center flex-wrap gap-8">
                    @foreach ($areas as $area)
                        <div class="group relative">
                            <!-- Area Card -->
                            <div
                                class="bg-gradient-to-br from-white to-gray-50 rounded-xl border-2 border-gray-200 hover:border-indigo-300 transition-all duration-300 overflow-hidden shadow-sm hover:shadow-xl transform hover:-translate-y-1">

                                <div
                                    class="bg-gradient-to-r 
    {{ $area['vip'] ? 'from-yellow-500 to-red-500' : 'from-indigo-500 to-purple-600' }} 
    text-white p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h3 class="font-bold text-lg flex items-center gap-2">
                                                {{ $area['name'] }}
                                                @if ($area['vip'])
                                                    <span
                                                        class="px-2 py-1 text-xs font-bold rounded bg-yellow-300 text-yellow-900 shadow">
                                                        VIP
                                                    </span>
                                                @endif
                                            </h3>
                                            <p class="text-indigo-100">{{ $area['capacity'] }} ghế</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button wire:click="selectArea('{{ $area['id'] }}')"
                                                class="bg-white/20 hover:bg-white/30 p-2 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button wire:click="deleteArea('{{ $area['id'] }}')"
                                                wire:confirm="Bạn có chắc chắn muốn xóa khu vực này?"
                                                class="bg-red-500/20 hover:bg-red-500/30 p-2 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="grid gap-1 max-h-48 overflow-hidden"
                                        style="grid-template-columns: repeat({{ max(1, 30) }}, minmax(0, 1fr));">
                                        @php
                                            $seatsToShow = array_slice($area['seats'] ?? [], 0, 50);
                                        @endphp
                                        @foreach ($seatsToShow as $seat)
                                            <div
                                                class="w-7 h-7 rounded-sm flex items-center justify-center text-xs font-medium
                                                @if ($seat['status'] == \App\Utils\Constants\EventSeatStatus::AVAILABLE->value) bg-green-200 text-green-800
                                                @elseif($seat['status'] == \App\Utils\Constants\EventSeatStatus::BOOKED->value) bg-red-200 text-red-800
                                                @else bg-gray-200 text-gray-600 @endif">
                                                {{ $seat['seat_code'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div x-data="{ showAreaModal: @entangle('showAreaModal') }">
        <div x-show="showAreaModal" x-transition.opacity
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
            style="display: none;">
            <div x-show="showAreaModal" x-transition.scale.origin.center
                class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">

                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Tạo Khu Vực Mới</h2>
                        <button @click="showAreaModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form wire:submit="createArea" class="space-y-6">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Số lượng chỗ ngồi
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" wire:model="areaCapacity"
                                        placeholder="Nhập số ghế" min="1" max="1000" required />
                                </x-filament::input.wrapper>
                                @error('areaCapacity')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex-shrink-0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Loại khu vực
                                </label>

                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="areaVip" class="sr-only">

                                </label>

                                <span
                                    class="ml-2 text-sm {{ $areaVip ? 'text-yellow-600 font-semibold' : 'text-gray-600' }}">
                                    {{ $areaVip ? 'VIP' : 'Thường' }}
                                </span>
                            </div>

                        </div>

                        <!-- Thông tin bổ sung nếu là VIP -->
                        @if ($areaVip)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-800">
                                        Khu vực VIP
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end items-center space-x-3 pt-6 border-t border-gray-200">
                            <x-filament::button type="button" @click="showAreaModal = false" color="gray"
                                size="sm">
                                <svg wire:loading.remove wire:target="createArea" class="w-4 h-4 mr-1" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Hủy bỏ
                            </x-filament::button>

                            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="createArea"
                                color="primary" size="sm">
                                <svg wire:loading.remove wire:target="createArea" class="w-4 h-4 mr-1" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span wire:loading.remove wire:target="createArea">Tạo Khu Vực</span>
                                <span wire:loading wire:target="createArea">Đang tạo...</span>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Seats Modal -->
    <div x-data="{ showSeatModal: @entangle('showSeatModal') }">
        <div x-show="showSeatModal" x-transition.opacity
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-40"
            style="display: none;">
            <div x-show="showSeatModal" x-transition.scale.origin.center
                class="bg-white rounded-2xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">Chỉnh sửa khu vực</h2>
                        <button wire:click="closeModalEdit()" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                    <div class="space-y-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tên khu vực -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Tên khu vực <span class="text-red-500">*</span>
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="text"
                                        wire:model.live.debounce.300ms="selectedArea.name"
                                        placeholder="VD: Khu vực A, Tầng 1..." required />
                                </x-filament::input.wrapper>
                                @error('selectedArea.name')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tổng số ghế -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Tổng số ghế <span class="text-red-500">*</span>
                                </label>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" wire:model.live="selectedArea.capacity"
                                        min="1" max="1000" placeholder="Nhập số ghế" required />
                                </x-filament::input.wrapper>
                                @error('selectedArea.capacity')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if ($selectedArea['capacity'] ?? 0 > 0)
                                    <p class="text-xs text-gray-500">
                                        <svg class="inline w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Sẽ tạo {{ $selectedArea['capacity'] }} ghế trong khu vực này
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Toggle VIP và thông tin -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Loại khu vực
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model.live="selectedArea.vip" class="sr-only">

                                    </label>

                                    <span
                                        class="text-sm font-medium {{ $selectedArea['vip'] ?? false ? 'text-yellow-600' : 'text-gray-400' }}">
                                        VIP
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-between mb-4">
                        <button wire:click="updateArea"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Lưu</button>
                        <div class="flex gap-2">
                            <button wire:click="closeModalEdit()"
                                class="px-4 py-2 bg-red-500 text-white rounded-lg">Đóng</button>
                        </div>
                    </div>

                    <div class="flex gap-2 mb-4">
                        <button wire:click="$set('seatFilter', 'all')"
                            class="px-3 py-1 rounded-lg text-sm 
            {{ $seatFilter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                            Tất cả
                        </button>
                        <button
                            wire:click="$set('seatFilter', {{ \App\Utils\Constants\EventSeatStatus::AVAILABLE->value }})"
                            class="px-3 py-1 rounded-lg text-sm 
            {{ $seatFilter === \App\Utils\Constants\EventSeatStatus::AVAILABLE->value
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-700' }}">
                            Trống
                        </button>
                        <button
                            wire:click="$set('seatFilter', {{ \App\Utils\Constants\EventSeatStatus::BOOKED->value }})"
                            class="px-3 py-1 rounded-lg text-sm 
            {{ $seatFilter === \App\Utils\Constants\EventSeatStatus::BOOKED->value
                ? 'bg-red-600 text-white'
                : 'bg-gray-200 text-gray-700' }}">
                            Đã đặt
                        </button>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-xl">
                        <div class="grid gap-2"
                            style="grid-template-columns: repeat(auto-fit, minmax(40px, max-content));">
                            @foreach ($this->paginatedEditingSeats as $seat)
                                <button wire:click="showDetailSeat('{{ $seat['id'] }}')"
                                    class="aspect-square rounded-lg font-bold text-sm transition hover:scale-110 hover:shadow-lg
                                @if ($seat['status'] == \App\Utils\Constants\EventSeatStatus::AVAILABLE->value) bg-green-500 hover:bg-green-600
                                @else bg-red-500 hover:bg-red-600 @endif text-white">
                                    {{ $seat['seat_code'] }}
                                </button>
                            @endforeach
                        </div>
                        <div class="my-2">
                            {{ $this->paginatedEditingSeats->links() }}
                        </div>
                    </div>
                    <div x-data="{ hiddenDetailSeat: @entangle('hiddenDetailSeat') }">
                        <div x-show="hiddenDetailSeat" x-transition.opacity
                            class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
                            style="display: none;">

                            <div x-show="hiddenDetailSeat" x-transition.scale
                                class="bg-white rounded-xl p-6 w-full max-w-2xl shadow-xl">

                                <h2 class="text-xl font-bold mb-4">Thông tin ghế</h2>

                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="font-semibold">Mã ghế:</span>
                                        <span>{{ $seatInfo['seat_code'] ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-semibold">Trạng thái:</span>
                                        <span class="capitalize">
                                            {{ \App\Utils\Constants\EventSeatStatus::tryFrom($seatInfo['status'] ?? 0)?->label() ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">Đổi tên ghế</label>
                                        <div class="flex mt-1 space-x-2">
                                            <input type="text" wire:model.defer="newSeatName"
                                                class="flex-1 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                            <button wire:click="updateSeatName"
                                                class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                                Lưu
                                            </button>
                                        </div>
                                        @error('newSeatName')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @if (!empty($seatUser))
                                    <h3 class="text-lg font-semibold mt-6">Người ngồi</h3>
                                    <div class="flex justify-between">
                                        <span>Tên:</span>
                                        <span>{{ $seatUser['name'] ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Email:</span>
                                        <span>{{ $seatUser['email'] ?? '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>SĐT:</span>
                                        <span>{{ $seatUser['phone'] ?? '-' }}</span>
                                    </div>

                                    <div class="mt-3">
                                        <button wire:click="removeSeatUser"
                                            class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                            Xóa người dùng khỏi ghế
                                        </button>
                                    </div>
                                @else
                                    <h3 class="text-lg font-semibold mt-6">Gán người dùng cho ghế</h3>
                                    <input type="text" wire:model.live.debounce.300ms="userSearch"
                                        placeholder="Tìm theo tên, email hoặc SĐT..."
                                        class="w-full px-3 py-2 border rounded-lg mb-3 focus:ring-2 focus:ring-indigo-500">

                                    <div class="space-y-4">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-left">Tên</th>
                                                    <th class="text-left">Email</th>
                                                    <th class="text-left">Điện thoại</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($this->paginatedUsers as $user)
                                                    <tr>
                                                        <td>{{ $user['name'] }}</td>
                                                        <td>{{ $user['email'] }}</td>
                                                        <td>{{ $user['phone'] }}</td>
                                                        <td class="px-2 py-1">
                                                            @php
                                                                $alreadyAssigned = in_array(
                                                                    $user['id'],
                                                                    $this->seatService->getAssignedUserIds(
                                                                        $this->event,
                                                                    ),
                                                                );
                                                            @endphp

                                                            <label
                                                                class="inline-flex items-center space-x-2 cursor-pointer">
                                                                <input type="radio" value="{{ $user['id'] }}"
                                                                    wire:model="selectedSeatUser"
                                                                    wire:click="toggleSelectedUser('{{ $user['id'] }}')"
                                                                    @disabled(($alreadyAssigned && $selectedSeatUser !== $user['id']) || ($selectedSeatUser && $selectedSeatUser !== $user['id']))
                                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                                    aria-label="Chọn {{ $user['name'] }}" />

                                                                <span class="text-sm">
                                                                    @if ($alreadyAssigned)
                                                                        <span
                                                                            class="text-xs text-red-600 font-medium">Đã
                                                                            có ghế</span>
                                                                    @else
                                                                        <span
                                                                            class="{{ $selectedSeatUser === $user['id'] ? 'text-green-600 font-semibold' : 'text-gray-700' }}">
                                                                            {{ $selectedSeatUser === $user['id'] ? 'Đã chọn' : 'Chọn' }}
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </label>

                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        {{ $this->paginatedUsers->links() }}
                                    </div>
                                @endif

                                <div class="flex justify-end mt-4 space-x-2">
                                    <button type="button" wire:click="closeDetailSeat"
                                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                                        Đóng
                                    </button>


                                    <button wire:click="assignSeatToUser" @disabled(!$selectedSeatUser)
                                        class="px-4 py-2 bg-indigo-600 cursor-pointer text-white rounded-lg hover:bg-indigo-700">
                                        Gán
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
