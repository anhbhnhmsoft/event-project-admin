<div class="space-y-3">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2 pr-2">{{ __('admin.notifications.table.user') }}</th>
                <th class="py-2 pr-2">{{ __('admin.notifications.table.status') }}</th>
                <th class="py-2 pr-2">{{ __('admin.notifications.table.time') }}</th>
            </tr>
        </thead>
        <tbody>
            @if ($record->user)
                <tr>
                    <td>{{ $record->user?->name }} ({{ $record->user?->email }})</td>
                    <td>
                        @php($label = \App\Utils\Constants\UserNotificationStatus::from((int) $record->status)->label())
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs">{{ $label }}</span>
                    </td>
                    <td>{{ $record->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="text-center text-gray-500">{{ __('admin.notifications.no_recipients') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
