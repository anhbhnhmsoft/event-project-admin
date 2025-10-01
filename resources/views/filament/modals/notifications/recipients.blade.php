<div class="space-y-3">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b">
                <th class="py-2 pr-2">User</th>
                <th class="py-2 pr-2">Trạng thái</th>
                <th class="py-2 pr-2">Thời gian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recipients as $item)
                <tr class="border-b last:border-0">
                    <td class="py-2 pr-2">{{ $item->user?->name }} ({{ $item->user?->email }})</td>
                    <td class="py-2 pr-2">
                        @php($label = $RecipientStatus::from((int) $item->status)->label())
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs">{{ $label }}</span>
                    </td>
                    <td class="py-2 pr-2">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="py-3 text-center text-gray-500">Chưa có người nhận</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


