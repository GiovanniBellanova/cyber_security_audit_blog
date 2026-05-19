<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RevisorController extends Controller
{
    public function dashboard(){
        $unrevisionedArticles = Article::where('is_accepted', NULL)->get();
        $acceptedArticles = Article::where('is_accepted', true)->get();
        $rejectedArticles = Article::where('is_accepted', false)->get();
        
        return view('revisor.dashboard', compact('unrevisionedArticles', 'acceptedArticles', 'rejectedArticles'));
    }

    public function acceptArticle(Article $article){
        $article->is_accepted = true;
        $article->save();

        // LOG DI SICUREZZA PER CHALLENGE 3
        Log::info("Il revisore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha ACCETTATO l'articolo: '" . $article->title . "'");

        return redirect(route('revisor.dashboard'))->with('message', 'Article Published');
    }

    public function rejectArticle(Article $article){
        $article->is_accepted = false;
        $article->save();

        // LOG DI SICUREZZA PER CHALLENGE 3
        Log::info("Il revisore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha RIFIUTATO l'articolo: '" . $article->title . "'");

        return redirect(route('revisor.dashboard'))->with('message', 'Article Declined');
    }

    public function undoArticle(Article $article){
        $article->is_accepted = NULL;
        $article->save();

        // LOG DI SICUREZZA PER CHALLENGE 3
        Log::info("Il revisore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha RIPORTATO IN REVISIONE l'articolo: '" . $article->title . "'");

        return redirect(route('revisor.dashboard'))->with('message', 'Article back to review');
    }
}