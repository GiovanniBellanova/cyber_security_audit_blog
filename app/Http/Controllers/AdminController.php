<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Tag;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\HttpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    protected $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
    } 

    public function dashboard(){
        $adminRequests = User::where('is_admin', NULL)->get();
        $revisorRequests = User::where('is_revisor', NULL)->get();
        $writerRequests = User::where('is_writer', NULL)->get();
        $financialData = []; // Inizializziamo la variabile come array vuoto
        
        try {
            // Effettua la richiesta HTTP alla Financial App interna [3, 4]
            $response = $this->httpService->getRequest('http://internal.finance:8001/user-data.php');
            
            if (empty($response)) {
                throw new Exception('La risposta dalla richiesta HTTP è vuota.');
            }
           
            $financialData = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Errore nella decodifica del JSON: ' . json_last_error_msg());
            }
        
        } catch (Exception $e) {
            echo 'Errore: ' . $e->getMessage();
            // Opzionale: Loggare anche il fallimento della connessione alla Financial App
            Log::error("Impossibile recuperare i dati finanziari: " . $e->getMessage());
        }
        
        return view('admin.dashboard', compact('adminRequests', 'revisorRequests', 'writerRequests','financialData'));
    }

    /* --- GESTIONE RUOLI (Challenge 3: Accountability) --- */

    public function setAdmin(User $user){
        $user->is_admin = true;
        $user->save();

        // Logga chi ha promosso l'utente per non-repudiation [1]
        Log::info("L'amministratore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha promosso l'utente " . $user->name . " (ID: " . $user->id . ") a ruolo ADMIN.");

        return redirect(route('admin.dashboard'))->with('message', "$user->name is now administrator");
    }

    public function setRevisor(User $user){
        $user->is_revisor = true;
        $user->save();

        Log::info("L'amministratore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha promosso l'utente " . $user->name . " (ID: " . $user->id . ") a ruolo REVISOR.");

        return redirect(route('admin.dashboard'))->with('message', "$user->name is now revisor");
    }

    public function setWriter(User $user){
        $user->is_writer = true;
        $user->save();

        Log::info("L'amministratore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha promosso l'utente " . $user->name . " (ID: " . $user->id . ") a ruolo WRITER.");

        return redirect(route('admin.dashboard'))->with('message', "$user->name is now writer");
    }

    /* --- GESTIONE TAG E CATEGORIE (Accountability completa) --- */

    public function editTag(Request $request, Tag $tag){
        $request->validate([
            'name' => 'required|unique:tags',
        ]);
        $oldName = $tag->name;
        $tag->update([
            'name' => strtolower($request->name),
        ]);

        Log::info("L'amministratore " . Auth::user()->name . " ha MODIFICATO il tag '$oldName' in '" . $tag->name . "'.");

        return redirect()->back()->with('message', 'Tag successfully updated');
    }

    public function deleteTag(Tag $tag){
        $tagName = $tag->name;
        foreach($tag->articles as $article){
            $article->tags()->detach($tag);
        }
        $tag->delete();

        Log::info("L'amministratore " . Auth::user()->name . " ha ELIMINATO il tag: " . $tagName);

        return redirect()->back()->with('message', 'Tag successfully deleted');
    }

    public function storeTag(Request $request){
        $tag = Tag::create([
            'name' => strtolower($request->name),
        ]);

        Log::info("L'amministratore " . Auth::user()->name . " ha CREATO il tag: " . $tag->name);
        
        return redirect()->back()->with('message', 'Tag successfully created');
    }

    public function editCategory(Request $request, Category $category){
        $request->validate([
            'name' => 'required|unique:categories',
        ]);
        $oldName = $category->name;
        $category->update([
            'name' => strtolower($request->name),
        ]);

        Log::info("L'amministratore " . Auth::user()->name . " ha MODIFICATO la categoria '$oldName' in '" . $category->name . "'.");

        return redirect()->back()->with('message', 'Category successfully updated');
    }

    public function deleteCategory(Category $category){
        $categoryName = $category->name; // Memorizza il nome prima della cancellazione
        $category->delete();

        Log::info("L'amministratore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha ELIMINATO la categoria: " . $categoryName);

        return redirect()->back()->with('message', 'Category successfully deleted');
    }

    public function storeCategory(Request $request){
        $category = Category::create([
            'name' => strtolower($request->name),
        ]);

        Log::info("L'amministratore " . Auth::user()->name . " (ID: " . Auth::user()->id . ") ha CREATO la categoria: " . $category->name);
        
        return redirect()->back()->with('message', 'Category successfully created');
    }
}