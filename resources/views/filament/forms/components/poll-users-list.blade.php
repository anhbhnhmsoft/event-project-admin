<div>
    @if($users->isEmpty())
        <p class="text-sm text-gray-500">Chưa có người tham gia nào</p>
    @else
        <div class="space-y-2">
            @foreach($users as $user)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <span class="text-sm font-medium">{{ $user->name }}</span>
                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-600">Tổng: {{ $users->count() }} người</p>
    @endif
</div>