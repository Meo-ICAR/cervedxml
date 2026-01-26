<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;

class LogIncomingApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Esegui la richiesta
        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000);

        try {
            // Non loggare file binari o dati troppo grandi se non necessario
            ApiLog::create([
                'direction'   => 'IN',
                'method'      => $request->method(),
                'url'         => $request->fullUrl(),
                'payload'     => $request->all(), // Attenzione: filtrare password qui se necessario
                'status_code' => $response->getStatusCode(),
                'response'    => json_decode($response->getContent(), true), 
                'ip_address'  => $request->ip(),
                'duration_ms' => $duration,
            ]);
        } catch (\Exception $e) {
            Log::error('Impossibile salvare log API: ' . $e->getMessage());
        }

        return $response;
    }
}