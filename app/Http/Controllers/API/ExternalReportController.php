<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ExternalReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'piva' => 'required|string|size:11',
        ]);

        $piva = $validated['piva'];

        try {
            // Find or create report
            $report = Report::where('piva', $piva)->first();

            if (!$report) {
                // If no report exists, create a basic one.
                // We need a user_id since it's not nullable.
                $user = User::first();

                $report = Report::create([
                    'piva' => $piva,
                    'name' => 'Pending Fetch...',
                    'id_soggetto' => '',
                    'codice_score' => '',
                    'descrizione_score' => '',
                    'valore' => 0,
                    'status' => 'pending',
                    'user_id' => $user ? $user->id : 1,
                ]);
            }

            // Invoke the command to fetch data from Cerved
            Artisan::call('cerved:fetch-score', ['piva' => $piva]);

            $report->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Report processing initiated',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ExternalReportController: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process report',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function uploadXml(Request $request)
    {
        $apiKey = $request->header('X-Api-Key');
        if ($apiKey !== env('MEDIAFACILE_HEADER_KEY')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'piva' => 'required|string|size:11',
            'file' => 'required|file|mimes:xml,txt',
        ]);

        try {
            $piva = $validated['piva'];
            $report = Report::where('piva', $piva)->first();

            if (!$report) {
                //  $user = User::first();

                $report = Report::create([
                    'piva' => $piva,
                    'name' => 'Pending Fetch...',
                    'status' => 'pending',
                    //   'user_id' => $user ? $user->id : 1,
                ]);
            }

            $report->addXmlFile($request->file('file'), $piva);

            return response()->json([
                'status' => 'success',
                'message' => 'XML file uploaded and attached to report',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ExternalReportController@uploadXml: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload XML',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
