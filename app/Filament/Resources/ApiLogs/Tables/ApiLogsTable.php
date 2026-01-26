<?php

namespace App\Filament\Resources\ApiLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\JsonEntry; // Se disponibile o usa Code block
class ApiLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('direction')
                ->colors([
                    'success' => 'IN',
                    'warning' => 'OUT',
                ]),
            BadgeColumn::make('method')
                ->colors(['gray']),
            BadgeColumn::make('status_code')
                ->colors([
                    'success' => fn ($state) => $state >= 200 && $state < 300,
                    'danger' => fn ($state) => $state >= 400,
                ]),
            TextColumn::make('url')->limit(50)->searchable(),
            TextColumn::make('duration_ms')->label('Duration (ms)')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
