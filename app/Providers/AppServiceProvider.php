<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

// CHALLENGE 1
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

// AGGIUNTE PER CHALLENGE 3 (Accountability Autenticazione)
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

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
        // Logica esistente per categorie e tag
        if(Schema::hasTable('categories')){
            $categories = Category::all();
            View::share(['categories' => $categories]);
        }
        if(Schema::hasTable('tags')){
            $tags = Tag::all();
            View::share(['tags' => $tags]);
        }

        // CHALLENGE 1: Definizione del Rate Limiter per la ricerca
        RateLimiter::for('search_limiter', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // --- MITIGAZIONE CHALLENGE 3: MONITORAGGIO EVENTI CRITICI ---

        // LOG PER IL LOGIN
        Event::listen(Login::class, function ($event) {
            Log::info("LOGIN: L'utente " . $event->user->name . " (ID: " . $event->user->id . ") ha effettuato l'accesso.");
        });

        // LOG PER IL LOGOUT
        Event::listen(Logout::class, function ($event) {
            Log::info("LOGOUT: L'utente " . $event->user->name . " (ID: " . $event->user->id . ") è uscito dal sistema.");
        });

        // LOG PER LA REGISTRAZIONE
        Event::listen(Registered::class, function ($event) {
            Log::info("REGISTRAZIONE: Nuovo utente registrato - " . $event->user->name . " (ID: " . $event->user->id . ")");
        });
    }
}