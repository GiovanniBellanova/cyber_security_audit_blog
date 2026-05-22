<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        // comando vulnerabile perché accetta tutto ciò che arriva dal form
        Auth::user()->update($request->all());

        return redirect()->back()->with('status', 'Profilo aggiornato!');
    }
}