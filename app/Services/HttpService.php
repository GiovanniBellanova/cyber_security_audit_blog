<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HttpService
{
    protected $client;
    protected $allowedDomains = ['internal.finance','newsapi.org'];
    protected $allowedProtocols = ['http', 'https'];
    protected $refererHeader; // Intestazione Referer

    public function __construct()
    {
        $this->refererHeader = config('app.url');
        $this->client = new Client();
    }

    public function getRequest($url)
    {
        $parsedUrl = parse_url($url);

        // Validate protocol
        if (!in_array($parsedUrl['scheme'], $this->allowedProtocols)) {
            return 'Protocol not allowed';
        }
       
        // Validate domain
        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $this->allowedDomains)) {
            return 'Domain not allowed';
        }

        // --- MITIGAZIONE CHALLENGE 4: CONTROLLO RUOLI PER ACCESSO INTERNO ---
        // Se l'host è 'internal.finance', controlliamo i privilegi dell'utente
        if ($parsedUrl['host'] === 'internal.finance') {
            if (!Auth::user() || !Auth::user()->is_admin) {
                // Logghiamo il tentativo per garantire accountability [3]
                Log::warning("SSRF PREVENTION: Utente non autorizzato (" . (Auth::user()->name ?? 'Guest') . ") ha tentato l'accesso a: " . $url);
            
            return 'Access Denied: You do not have permission to access internal resources.';
        }
    }
        // Aggiungi l'intestazione Referer per le richieste al server locale
        $options['headers'] = ['Referer' => $this->refererHeader];

        try {
            $response = $this->client->request('GET', $url, $options);
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            return 'Something went wrong: ' . $e->getMessage();
        }
    }
}
