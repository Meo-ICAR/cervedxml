<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $reports = Report::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'piva' => 'required|string|size:11',
            'is_racese' => 'sometimes|boolean',
            'annotation' => 'nullable|string',
            'idsoggetto' => 'required|string|max:20',
            'codice_score' => 'required|string|max:20',
            'descrizione_score' => 'required|string|max:255',
            'valore' => 'required|numeric|between:0,99999999.99',
            'xml_file' => 'required|file|mimes:xml|max:10240',  // Max 10MB
        ]);

        try {
            $report = new Report($validated);
            $report->user_id = Auth::id();
            $report->status = 'draft';
            $report->save();

            // Handle XML file upload
            if ($request->hasFile('xml_file')) {
                $report->addXmlFile($request->file('xml_file'));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Report created successfully',
                'data' => $report->load('media')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        return response()->json([
            'status' => 'success',
            'data' => $report->load('media')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'piva' => 'sometimes|string|size:11',
            'is_racese' => 'sometimes|boolean',
            'annotation' => 'nullable|string',
            'idsoggetto' => 'sometimes|string|max:20',
            'codice_score' => 'sometimes|string|max:20',
            'descrizione_score' => 'sometimes|string|max:255',
            'valore' => 'sometimes|numeric|between:0,99999999.99',
            'status' => ['sometimes', 'string', Rule::in(['draft', 'submitted', 'processed', 'archived'])],
            'xml_file' => 'sometimes|file|mimes:xml|max:10240',
        ]);

        try {
            $report->update($validated);

            // Handle XML file update if provided
            if ($request->hasFile('xml_file')) {
                $report->clearMediaCollection('xml_files');
                $report->addXmlFile($request->file('xml_file'));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Report updated successfully',
                'data' => $report->load('media')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        try {
            // Delete associated media files
            $report->clearMediaCollection('xml_files');

            // Soft delete the report
            $report->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Upload an XML file to an existing report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Report  $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadXml(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:10240',
        ]);

        try {
            // Clear existing XML files and add the new one
            $report->clearMediaCollection('xml_files');
            $media = $report->addXmlFile($request->file('xml_file'));

            return response()->json([
                'status' => 'success',
                'message' => 'XML file uploaded successfully',
                'data' => [
                    'file_url' => $media->getUrl(),
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading XML file: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload XML file',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
