<?php

namespace App\Filament\Resources\Memberships\Tables;

use App\Models\Membership;
use App\Utils\Constants\MembershipType;
use App\Utils\Constants\RoleUser;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MembershipsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $query = Membership::query();

        if ($user->role === RoleUser::SUPER_ADMIN->value) {
            $query->where('organizer_id', $user->organizer_id);
        } elseif ($user->role === RoleUser::ADMIN->value) {
            $query->where('organizer_id', $user->organizer_id)
                ->where('type', MembershipType::FOR_CUSTOMER->value);
        }
        return $table
            ->query(fn() => $query)
            ->columns([
                TextColumn::make('name')
                    ->label('Tên gói'),
                TextColumn::make('badge')
                    ->label('Huy hiệu hiển thị'),
                TextColumn::make('price')
                    ->label('Giá')
                    ->numeric(0, ',', '.'),
                IconColumn::make('status')
                    ->label("Trạng thái hoạt động")
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('duration')
                    ->label('Thời hạn gói'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label("Sửa"),
                DeleteAction::make()
                    ->label('Xóa')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa'),
                ]),
            ]);
    }
}
