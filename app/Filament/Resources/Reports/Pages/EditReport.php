<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateXmlCompleto')
                ->label('Genera XML Completo')
                ->color('success')
                ->icon('heroicon-o-document-plus')
                ->action(function () {
                    try {
                        $this->record->generateCompleteXml();
                        
                        Notification::make()
                            ->title('XML Completo generato con successo')
                            ->success()
                            ->send();
                            
                        $this->refreshFormData(['xml_completo']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Errore durante la generazione')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
