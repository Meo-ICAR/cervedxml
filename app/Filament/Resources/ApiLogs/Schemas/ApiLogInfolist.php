<?php

namespace App\Filament\Resources\ApiLogs\Schemas;

use Filament\Schemas\Schema;

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
