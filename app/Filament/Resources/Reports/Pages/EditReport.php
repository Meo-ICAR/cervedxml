<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use App\Services\CervedXmlParser;
use Barryvdh\DomPDF\Facade\Pdf;
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
            Action::make('parseAndGenerate')
                ->label('Parse Cerved & Genera PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Genera Report da XML')
                ->modalDescription('Elabora XML Cerved e crea PDF formattato.')
                ->modalSubmitActionLabel('Genera PDF')
                ->action(function () {
                    /** @var Report $record */
                    $record = $this->record;  // ← prendi il model dalla Page
                    $record->generateCompleteXml();
                    $parsed = $record->parseCervedXml();

                    $jsonData = json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $record
                        ->addMediaFromString($jsonData)
                        ->usingName('cerved_' . $record->piva . '.json')
                        ->withCustomProperties([
                            'mime_type' => 'application/json',
                            'parsed_at' => now(),
                        ])
                        ->toMediaCollection('parsed_data');

                    $pdf = Pdf::loadView('filament.reports.xml-print', [
                        'record' => $record,
                        'data' => $parsed,
                    ])
                        ->setPaper('a4', 'portrait')
                        ->setOptions([
                            'defaultFont' => 'DejaVu Sans',
                            'isHtml5ParserEnabled' => true,
                            'isPhpEnabled' => true,
                        ]);

                    $pdfPath = storage_path(
                        'app/temp/cerved_' . $record->piva . '.pdf'
                    );
                    if (!is_dir(dirname($pdfPath))) {
                        mkdir(dirname($pdfPath), 0755, true);
                    }
                    $pdf->save($pdfPath);

                    $record
                        ->addMedia($pdfPath)
                        ->usingName('cerved_report_pdf_' . $record->piva)
                        ->withCustomProperties([
                            'mime_type' => 'application/pdf',
                            'generated_from' => 'xml_parse',
                            'parsed_data_id' => $record->getMedia('parsed_data')->last()?->id,
                        ])
                        ->toMediaCollection('reports');

                    @unlink($pdfPath);

                    Notification::make()
                        ->title('Report generato!')
                        ->body('PDF e dati parsed salvati in Media Library.')
                        ->success()
                        ->send();

                    $this->record->refresh();
                })
                ->visible(fn() => $this->record->getFirstMedia('xml_files'))
                ->tooltip('Richiede file XML in "xml_files" collection'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
            ];

}
}
