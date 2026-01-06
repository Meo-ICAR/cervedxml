<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CervedService
{
    /**
     * Upload an XML file to Cerved report.
     *
     * @param string $filePath Absolute path to the XML file
     * @param string $piva VAT number
     * @return \Illuminate\Http\Client\Response
     */
    public function uploadXml(string $filePath, string $piva)
    {
        $apiKey = env('MEDIAFACILE_HEADER_KEY');
        $url = 'https://cerved.hassisto.com/api/upload-xml';

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
            ])->attach(
                'file', file_get_contents($filePath), basename($filePath)
            )->post($url, [
                'piva' => $piva,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Error uploading XML to Cerved', [
                'error' => $e->getMessage(),
                'piva' => $piva,
                'filePath' => $filePath,
            ]);
            throw $e;
        }
    }
}
