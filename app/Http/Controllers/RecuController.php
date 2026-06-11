<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\StoreRecuRequest;
use App\Models\Recu;

class RecuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recus = auth()->user()
            ->recus()
            ->withCount('depenses')
            ->latest()
            ->get();

        return view('recus.index', compact('recus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecuRequest $request)
    {
        auth()->user()->recus()->create([
            'texte_brut' => $request->validated()['texte_brut'],
            'statut'       => 'en_attente',
        ]);

        return redirect()->route('recus.index')
            ->with('success', 'Reçu soumis avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Recu $recu): View
    {
        abort_if($recu->user_id !== auth()->id(), 403);

        $recu->load('depenses');

        return view('recus.show', compact('recu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recu $recu): RedirectResponse
    {
        abort_if($recu->user_id !== auth()->id(), 403);

        $recu->delete();

        return redirect()->route('recus.index')
            ->with('success', 'Reçu supprimé.');
    }
}
