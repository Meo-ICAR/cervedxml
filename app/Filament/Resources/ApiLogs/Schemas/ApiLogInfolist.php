<?php

namespace App\Filament\Resources\ApiLogs\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schema\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\JsonEntry; // Se dispon
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontFamily;     // Necessario per il font monospaziato

class ApiLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
             
            TextEntry::make('url')->columnSpanFull(),
            TextEntry::make('method'),
            TextEntry::make('status_code'),
            
            // Per visualizzare bene il JSON
            TextEntry::make('payload')
                ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),
                
            TextEntry::make('response')
                ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                ->fontFamily(FontFamily::Mono)
                ->columnSpanFull(),
        ]);
}
            
}
