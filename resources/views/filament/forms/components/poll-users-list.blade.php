<div>
    @if($users->isEmpty())
        <p class="text-sm text-gray-500">{{ __('admin.events.poll.no_participants') }}</p>
    @else
        <div class="space-y-2">
            @foreach($users as $user)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <span class="text-sm font-medium">{{ $user->name }}</span>
                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-600">{{ __('admin.events.poll.total') }}: {{ $users->count() }} {{ __('admin.events.poll.people') }}</p>
    @endif
</div>