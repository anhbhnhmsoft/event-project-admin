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
                    ->label(__('admin.memberships.table.name')),
                TextColumn::make('badge')
                    ->label(__('admin.memberships.table.badge')),
                TextColumn::make('price')
                    ->label(__('admin.memberships.table.price'))
                    ->numeric(0, ',', '.'),
                IconColumn::make('status')
                    ->label(__('admin.memberships.table.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('duration')
                    ->label(__('admin.memberships.table.duration')),
                TextColumn::make('type')
                    ->label(__('admin.memberships.table.type'))
                    ->formatStateUsing(fn($state) => MembershipType::label($state))
                    ->hidden(fn() => Auth::user()->role != RoleUser::SUPER_ADMIN->value),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label(__('common.common_success.edit')),
                DeleteAction::make()
                    ->label(__('common.common_success.delete'))
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('common.common_success.delete')),
                ]),
            ]);
    }
}
