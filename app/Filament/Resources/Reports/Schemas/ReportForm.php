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
                    ->label('P.IVA')
                    ->length(11),
                TextInput::make('name')
                    ->hidden(fn($operation) => $operation === 'create')
                    ->label('Denominazione')
                    ->required(fn($operation) => $operation !== 'create'),
                SpatieMediaLibraryFileUpload::make('xml_files')
                    ->collection('xml_files')
                    ->label('File XML Originale')
                    ->acceptedFileTypes(['application/xml', 'text/xml'])
                    ->downloadable()
                    ->openable()
                    ->visible((fn($operation) => $operation !== 'create'))
                    ->columnSpanFull(),
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
                SpatieMediaLibraryFileUpload::make('xml_completo')
                    ->collection('xml_completo')
                    ->label('File XML Completo')
                    ->acceptedFileTypes(['application/xml', 'text/xml'])
                    ->downloadable()
                    ->visible(fn($record, $operation) =>
                        $operation !== 'create' &&
                        $record &&
                        $record->isComplete())
                    ->openable()
                    ->deletable(false)
                    ->dehydrated(false)
                    //  ->displayFileName()
                    ->columnSpanFull(),
                Section::make('Anteprima XML Completo')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        ViewField::make('xml_content_preview')
                            ->view('filament.reports.xml-preview')
                            ->formatStateUsing(fn($record) => $record?->getXmlAsHtmlTable())
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record, $operation) =>
                        $operation !== 'create' &&
                        $record &&
                        $record->isCompiled())
                    ->columnSpanFull(),
            ]);
    }
}
