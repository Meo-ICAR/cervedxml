<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyMediafacileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cerved:notify-mediafacile {piva : The P.IVA to notify for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to Mediafacile for a specific P.IVA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $piva = $this->argument('piva');
        $baseUrl = env('MEDIAFACILE_BASE_URL');
        $apiKey = env('MEDIAFACILE_HEADER_KEY');

        if (!$baseUrl || !$apiKey) {
            $this->error('MEDIAFACILE_BASE_URL or MEDIAFACILE_HEADER_KEY not set in .env');
            return 1;
        }

        $url = "{$baseUrl}?table=cerved&piva={$piva}";

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
            ])->post($url);

            if ($response instanceof Response) {
                if ($response->successful()) {
                    $this->info("Notification sent successfully for P.IVA: {$piva}");
                    return 0;
                } else {
                    $this->error("Failed to send notification. Status: " . $response->status());
                    $this->error("Response: " . $response->body());
                    return 1;
                }
            }

            return 1;
        } catch (\Exception $e) {
            $this->error("Exception occurred: " . $e->getMessage());
            Log::error("Mediafacile Notification Error", [
                'piva' => $piva,
                'error' => $e->getMessage(),
            ]);
            return 1;
        }
    }
}
