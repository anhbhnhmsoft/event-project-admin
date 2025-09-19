<div class="min-h-screen bg-gradient-to-br from-slate-100 to-indigo-200
from-gray-100 via-indigo-100 to-purple-200
 p-6">
    <!-- Header -->
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
    <!-- Venue Layout -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div wire:loading wire:target="deleteArea,selectArea,loadAreas"
            class="fixed inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-20 rounded-2xl">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-indigo-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <p class="text-gray-600 font-medium">Đang xử lý...</p>
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

        <!-- Areas Grid -->
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
                                <!-- Area Header -->
                                <div
                                    class="bg-gradient-to-r 
    {{ $area['vip'] ? 'from-yellow-500 to-red-500' : 'from-indigo-500 to-purple-600' }} 
    text-white p-4">


                                    <button wire:click="deleteArea('{{ $area['id'] }}')"
                                        wire:confirm="Bạn có chắc chắn muốn xóa khu vực này?"
                                        class="bg-red-500/20 hover:bg-red-500/30 p-2 rounded-lg transition-colors relative"
                                        wire:loading.attr="disabled" wire:target="deleteArea('{{ $area['id'] }}')">
                                        <svg wire:loading.remove wire:target="deleteArea('{{ $area['id'] }}')"
                                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <svg wire:loading wire:target="deleteArea('{{ $area['id'] }}')"
                                            class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z">
                                            </path>
                                        </svg>
                                    </button>

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
                                        style="grid-template-columns: repeat({{ max(1, $area['seats_per_row']) }}, minmax(0, 1fr));">
                                        @php
                                            $seatsToShow = array_slice($area['seats'] ?? [], 0, 50);
                                        @endphp
                                        @foreach ($seatsToShow as $seat)
                                            <div
                                                class="w-7 h-7 rounded-sm flex items-center justify-center text-xs font-medium
                                                @if ($seat['status'] == \App\Utils\Constants\EventSeatStatus::AVAILABLE->value) bg-green-200 text-green-800
                                                @elseif($seat['status'] == \App\Utils\Constants\EventSeatStatus::RESERVED->value) bg-yellow-200 text-yellow-800  
                                                @elseif($seat['status'] == \App\Utils\Constants\EventSeatStatus::BOOKED->value) bg-red-200 text-red-800
                                                @else bg-gray-200 text-gray-600 @endif">
                                                {{ $seat['seat_code'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                    @if (count($area['seats'] ?? []) > 50)
                                        <div class="text-center mt-2 text-gray-500 text-sm">
                                            ... và {{ count($area['seats']) - 50 }} ghế khác
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <!-- Create Area Modal -->
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
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Tổng số ghế</label>
                                <input type="number" wire:model="areaCapacity"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    min="1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">Ghế mỗi hàng</label>
                                <input type="number" wire:model="areaSeatsPerRow"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    min="1">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2">VIP</label>
                                <button type="button" wire:click="$toggle('areaVip')"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $areaVip ? 'bg-indigo-600' : 'bg-gray-300' }}">
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $areaVip ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" @click="showAreaModal = false"
                                class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium">Hủy</button>

                            {{-- Nút loading --}}
                            <button type="submit" wire:loading.remove wire:target="createArea"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2 rounded-lg font-semibold transition transform hover:scale-105">
                                Tạo Khu Vực
                            </button>

                            <button type="button" wire:loading wire:target="createArea" disabled
                                class="bg-indigo-400 text-white px-8 py-2 rounded-lg font-semibold flex items-center space-x-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                <span>Đang tạo...</span>
                            </button>
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

                    <!-- Form sửa khu vực -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium">Tên khu vực</label>
                            <input type="text" wire:model="selectedArea.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Ghế mỗi hàng</label>
                            <input type="number" wire:model="selectedArea.seats_per_row" min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Tổng số ghế</label>
                            <input type="number" wire:model="selectedArea.capacity" min="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">VIP</label>
                            <button type="button" wire:click="$toggle('areaVip')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $areaVip ? 'bg-indigo-600' : 'bg-gray-300' }}">
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $areaVip ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
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
                            wire:click="$set('seatFilter', {{ \App\Utils\Constants\EventSeatStatus::RESERVED->value }})"
                            class="px-3 py-1 rounded-lg text-sm 
            {{ $seatFilter === \App\Utils\Constants\EventSeatStatus::RESERVED->value
                ? 'bg-yellow-600 text-white'
                : 'bg-gray-200 text-gray-700' }}">
                            Chờ
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
                                <button
                                    wire:click="{{ !empty($seat['user_id']) ? "showDetailSeat('{$seat['id']}')" : "selectSeat('{$seat['id']}')" }}"
                                    class="aspect-square rounded-lg font-bold text-sm transition hover:scale-110 hover:shadow-lg
                                @if ($seat['status'] == \App\Utils\Constants\EventSeatStatus::AVAILABLE->value) bg-green-500 hover:bg-green-600
                                @elseif($seat['status'] == \App\Utils\Constants\EventSeatStatus::RESERVED->value) bg-yellow-500 hover:bg-yellow-600
                                @else bg-red-500 hover:bg-red-600 @endif text-white">
                                    {{ $seat['seat_code'] }}
                                </button>
                            @endforeach
                        </div>
                        <div class="my-2">
                            {{ $this->paginatedEditingSeats->links() }}
                        </div>
                    </div>
                    <div x-data="{ showAssignUser: @entangle('showAssignUser') }">
                        <div x-show="showAssignUser" x-transition.opacity
                            class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
                            style="display: none;">
                            <div x-show="showAssignUser" x-transition.scale
                                class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
                                @if ($selectedSeat)
                                    <h3 class="font-semibold text-lg mb-4">Ghế: {{ $selectedSeat['seat_code'] }}</h3>
                                @endif
                                <input type="text" wire:model.live.debounce.300ms="userSearch"
                                    placeholder="Tìm theo tên, email hoặc SĐT..."
                                    class="w-full px-3 py-2 border rounded-lg mb-3 focus:ring-2 focus:ring-indigo-500">

                                <select wire:model="selectedSeatUser"
                                    class="mt-2 block w-full rounded-md border-gray-300">
                                    <option value="">-- Chọn người dùng --</option>
                                    @foreach ($this->paginatedUsers as $user)
                                        <option value="{{ $user['id'] }}">
                                            {{ $user['name'] }}
                                            ({{ $user['email'] }}{{ $user['phone'] ? ' - ' . $user['phone'] : '' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="my-2">
                                    {{ $this->paginatedUsers->links() }}
                                </div>
                                <div class="flex justify-end mt-4 space-x-2">
                                    <button @click="showAssignUser = false"
                                        class="px-4 py-2 bg-gray-200 rounded-lg">Hủy</button>
                                    <button wire:click="assignSeatToUser"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Gán</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div x-data="{ showSeatUser: @entangle('showSeatUser') }">
                        <div x-show="showSeatUser" x-transition.opacity
                            class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
                            style="display: none;">

                            <div x-show="showSeatUser" x-transition.scale
                                class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">

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

                                    @if (!empty($seatUser))
                                        <h3 class="text-lg font-semibold mt-4">Người ngồi</h3>
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
                                    @else
                                        <p class="text-gray-500 mt-4">Ghế này hiện chưa có người đặt.</p>
                                    @endif
                                </div>

                                <div class="flex justify-end mt-6 space-x-2">
                                    <button @click="showSeatUser = false"
                                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Đóng</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
