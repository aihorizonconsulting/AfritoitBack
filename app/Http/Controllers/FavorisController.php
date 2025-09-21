<?php

namespace App\Http\Controllers;

use App\Models\Favoris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavorisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $favoris = Favoris::with(['utilisateur', 'logement'])->get();
            return response()->json($favoris, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve favorites - FavorisController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve favorites: '], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $favoris = Favoris::create([
                'logement_id' => $request->logement_id,
                'client_id' => $request->client_id,
                'status' => $request->status ?? 'active',
                'date_ajout' => now(),
            ]);
            return response()->json($favoris, 201);
        } catch (\Exception $e) {
            Log::error('Failed to add favorite - FavorisController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add favorite'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $favoris = Favoris::with(['utilisateur', 'logement'])->findOrFail($id);
            return response()->json($favoris, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve favorite - FavorisController : ' . $e->getMessage());
            return response()->json(['error' => 'Favorite not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        try {
            $favoris = Favoris::findOrFail($id);
            $favoris->update($request->all());
            return response()->json($favoris, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update favorite - FavorisController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update favorite'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $favoris = Favoris::findOrFail($id);
            $favoris->delete();
            return response()->json(['message' => 'Favorite deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete favorite - FavorisController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete favorite'], 500);
        }
    }
}
