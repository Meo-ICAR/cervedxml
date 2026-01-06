<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('piva')
                    ->label('P.IVA')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Denominazione')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('valore')
                    ->label('ECOFIN')
                    ->numeric(),

                TextColumn::make('annotation')
                    ->label('Note')
                    ->limit(50),

                TextColumn::make('updated_at')
                    ->label('Aggiornato')
                    ->dateTime()
                    ->sortable(),
                //  ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
