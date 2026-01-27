<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schema\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('piva')
                    ->required()
                    ->label('Partita IVA azienda cliente')
                    ->length(11)
                    ->id('report-piva')
                    ->name('piva'),
                TextInput::make('name')
                    ->hidden(fn($operation) => $operation === 'create')
                    ->label('Denominazione azienda cliente')
                    ->required(fn($operation) => $operation !== 'create')
                    ->id('report-name')
                    ->name('name'),

                TextInput::make('valore')
                    ->hidden(fn($operation) => $operation === 'create')
                    ->required(fn($operation) => $operation !== 'create')
                    ->label('ECOFIN Score')
                    ->numeric(),
                TextInput::make('categoria_descrizione')
                    ->hidden(fn($operation) => $operation === 'create')
                    ->required(fn($operation) => $operation !== 'create')
                    ->label('Classificazione ECOFIN'),
                TextEntry::make('status')
                    ->hidden(fn($operation) => $operation === 'create')
                    ->disabled()
                    ->badge()
                    ->default('draft'),
                Textarea::make('annotation')
                    ->hidden(fn($operation) => $operation === 'create')
                    ->label('Note')
                    ->columnSpanFull(),

                SpatieMediaLibraryFileUpload::make('xml_files')
                    ->collection('xml_files')
                    ->label('File XML da Cerved / Mediafacile')
                    ->acceptedFileTypes(['application/xml', 'text/xml'])
                    ->deletable(false)
                    ->downloadable()
                    ->openable()
                    ->visible(fn($operation) => $operation !== 'create')
                    ->columnSpanFull()
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return $record->piva . '.xml';
                    }),

                SpatieMediaLibraryFileUpload::make('report_print')
                    ->collection('reports')
                    ->label('File Report Print')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable()

                    ->visible(fn($record, $operation) =>
                        $operation !== 'create' &&
                        $record &&
                        $record->isComplete())

                    ->openable()
                    ->deletable(true)
                    ->dehydrated(false)
                    //  ->displayFileName()
                    ->columnSpanFull(),

            ]);
    }
}
