<?php

namespace App\Http\Controllers;

use App\Enums\CategorieDepense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DepenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Auth::user()
            ->recus()
            ->with('depenses')
            ->latest();

        if ($categorie = $request->query('categorie')) {
            $query->whereHas('depenses', fn ($q) => $q->where('categorie', $categorie));
        }

        $recus = $query->get();
        $depenses = $recus->flatMap->depenses;
        $categories = CategorieDepense::cases();

        return view('depenses.index', compact('depenses', 'categories'));
    }
}
