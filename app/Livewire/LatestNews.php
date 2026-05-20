<?php

namespace App\Livewire;

use GuzzleHttp\Client;
use Livewire\Component;
use App\Services\HttpService;

use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth; 

class LatestNews extends Component
{
    public $selectedApi;
    public $news;
    protected $httpService;

    public function __construct()
    {
        $this->httpService = app(HttpService::class);
    }

    public function fetchNews()
    {

        // --- MITIGAZIONE CHALLENGE 4: WHITELIST DELLE API AUTORIZZATE ---
        $allowedApis = [
            "https://newsapi.org/v2/top-headlines?country=it&apiKey=5fbe92849d5648eabcbe072a1cf91473",
            "https://newsapi.org/v2/top-headlines?country=gb&apiKey=5fbe92849d5648eabcbe072a1cf91473",
            "https://newsapi.org/v2/top-headlines?country=us&apiKey=5fbe92849d5648eabcbe072a1cf91473"
        ];



        // Verifichiamo che l'URL selezionato sia esattamente uno di quelli previsti
        if (!in_array($this->selectedApi, $allowedApis)) {
            Log::warning("SSRF TENTATO (Whitelist): L'utente " . Auth::user()->name . " ha inserito un URL non autorizzato: " . $this->selectedApi);

            $this->news = 'Invalid or Unauthorized URL';
            // Opzionale: aggiunge un messaggio di errore visibile nella pagina
            return session()->flash('error', 'Sorgente news non valida o non autorizzata.');
        }

        // Se l'URL è nella whitelist, procediamo con la chiamata sicura
        $this->news = json_decode($this->httpService->getRequest($this->selectedApi), true);
    }


    public function render()
    {
        return view('livewire.latest-news');
    }
}
