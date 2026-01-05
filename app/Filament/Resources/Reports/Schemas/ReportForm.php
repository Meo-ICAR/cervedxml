<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('piva')
                    ->required(),
                Toggle::make('is_racese')
                    ->required(),
                Textarea::make('annotation')
                    ->columnSpanFull(),
                TextInput::make('idsoggetto')
                    ->required(),
                TextInput::make('codice_score')
                    ->required(),
                TextInput::make('descrizione_score')
                    ->required(),
                TextInput::make('valore')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
            ]);
    }
}
