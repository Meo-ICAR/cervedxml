<?php

namespace App\Filament\Resources\Reports\Tables;

use App\Models\Report;  // Assicurati di importare il modello corretto
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
// use Filament\Tables\Columns\TextEntry;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
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
                IconColumn::make('has_xml')
                    ->label('XML')
                    ->boolean()
                    ->getStateUsing(fn(Report $record): bool => $record->hasMedia('xml_files'))  // Corretto qui
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('annotation')
                    ->label('Note')
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->default('draft'),
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
                /*
                 * Action::make('view_xml')
                 *     ->label('XML')
                 *     ->icon('heroicon-o-code-bracket')
                 *     ->color('primary')
                 *     ->visible(fn(Report $record): bool => !empty($record->name) && $record->hasMedia('xml_files'))
                 *     ->url(fn(Report $record): string => route('filament.admin.resources.reports.view-xml', $record))
                 *     ->openUrlInNewTab(),
                 */
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
