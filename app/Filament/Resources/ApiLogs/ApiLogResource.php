<?php

namespace App\Filament\Resources\ApiLogs;

use App\Filament\Resources\ApiLogs\Pages\ListApiLogs;
use App\Filament\Resources\ApiLogs\Pages\ViewApiLog;
use App\Filament\Resources\ApiLogs\Schemas\ApiLogForm;
use App\Filament\Resources\ApiLogs\Schemas\ApiLogInfolist;
use App\Filament\Resources\ApiLogs\Tables\ApiLogsTable;
use App\Models\ApiLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;

class ApiLogResource extends Resource
{
    protected static ?string $model = ApiLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ApiLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApiLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiLogs::route('/'),
            'view' => ViewApiLog::route('/{record}'),
        ];
    }
}
