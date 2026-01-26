<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event; // Importante
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Events\ConnectionFailed;
use App\Models\ApiLog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Intercetta quando una risposta viene ricevuta (anche 404, 500, ecc.)
        Event::listen(function (ResponseReceived $event) {
            $request = $event->request;
            $response = $event->response;

            // Nota: Gli eventi HTTP di Laravel non forniscono nativamente la durata.
            // Se la durata è critica per le chiamate OUT, servirebbe una logica più complessa.
            
            try {
                ApiLog::create([
                    'direction'   => 'OUT',
                    'method'      => $request->method(),
                    'url'         => $request->url(),
                    // $request->data() può essere array o stringa, assicuriamoci sia salvabile
                    'payload'     => $this->formatPayload($request->data()),
                    'status_code' => $response->status(),
                    'response'    => $response->json(), // O $response->body() se non è JSON
                    'ip_address'  => null, 
                ]);
            } catch (\Exception $e) {
                // Evitiamo che il log blocchi l'app
            }
        });

        // 2. Intercetta quando la connessione fallisce (es. timeout, DNS down)
        Event::listen(function (ConnectionFailed $event) {
            try {
                ApiLog::create([
                    'direction'   => 'OUT',
                    'method'      => $event->request->method(),
                    'url'         => $event->request->url(),
                    'payload'     => $this->formatPayload($event->request->data()),
                    'status_code' => 0, // 0 indica errore di connessione
                    'response'    => ['error' => 'Connection Failed (Timeout/DNS)'],
                ]);
            } catch (\Exception $e) {
                // Silenzia errori di log
            }
        });
    }

    /**
     * Helper per formattare il payload in modo sicuro per il DB
     */
    private function formatPayload($data)
    {
        if (is_array($data)) {
            return $data;
        }
        // Se è una stringa (es. raw body), proviamo a decodificarla o salvarla così com'è
        // Nota: Il cast 'array' nel model ApiLog si aspetta un array o JSON valido.
        return ['raw_body' => $data];
    }
}