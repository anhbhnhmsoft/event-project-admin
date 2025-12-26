<?php

namespace App\Filament\Resources\Members\Tables;

use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Constants\RoleUser;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.members.columns.user_name'))
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('avatar')
                    ->label(__('admin.members.columns.avatar')),
                TextColumn::make('email')
                    ->label(__('admin.members.columns.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('admin.members.columns.phone'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label(__('admin.members.columns.address'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('memberships')
                    ->label(__('admin.members.columns.memberships'))
                    ->formatStateUsing(function ($state) {
                        if (!$state) return null;
                        $endDate = $state->pivot->end_date;
                        return $state->name . ': ' .
                            MembershipUserStatus::from($state->pivot->status)->label() .
                            ' : ' . __('admin.members.columns.time_expired') . ' : ' .
                            ($endDate ?? Carbon::parse($endDate)->format('d/m/Y'));
                    })
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable()
            ])
            ->filters([
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
            ]);
    }
}
