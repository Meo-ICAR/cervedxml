<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class CreateReport extends CreateRecord
{
    protected static string $resource = ReportResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $existingReport = Report::where('piva', $data['piva'])->first();

        if ($existingReport) {
            $this->redirect($this->getResource()::getUrl('edit', ['record' => $existingReport]));

            return $existingReport;
        }

        $data['user_id'] = Auth::id();
        $data['name'] = 'Pending Fetch...';
        $data['id_soggetto'] = '';
        $data['codice_score'] = '';
        $data['descrizione_score'] = '';
        $data['valore'] = 0;
        $data['status'] = 'pending';

        $record = parent::handleRecordCreation($data);

        Artisan::call('cerved:fetch-score', ['piva' => $record->piva]);
        Artisan::call('cerved:notify-mediafacile', ['piva' => $record->piva]);

        return $record;
    }
}
