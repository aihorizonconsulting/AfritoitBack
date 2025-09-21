<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $produits = Produit::with('categorie')->with('images','utilisateur')->get();
            return response()->json($produits, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve products - ProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve products'], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
              try {
            $request->validate([
                'images.*' => 'file|max:51200',
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'prix' => 'required|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'etat' => 'nullable|string|max:50',
                'disponibilite' => 'nullable|string|max:50',
                'categorie_id' => 'required|exists:categories,id',
                'utilisateur_id' => 'nullable|exists:utilisateurs,idU',
            ], [
                'images.*.max' => 'Chaque image ne doit pas dépasser 50 Mo.'
            ]);


            $produit = new Produit();
            $produit->fill($request->except('images'));
            $produit->save();

            // Traiter les images si présentes
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    $destinationPath = public_path('imagesP');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($destinationPath, $filename);

                    $produit->images()->create([
                        'chemin' => 'imagesP/' . $filename,
                        'alt' => $file->getClientOriginalName(),
                    ]);
                }
            }

            return response()->json($produit->load('images','categorie'), 201);
        } catch (\Exception $e) {
            Log::error('Error creating produit - ProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create produit'], 500);
        }

        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $produit = Produit::with('categorie')->with('images')->findOrFail($id);
            return response()->json($produit, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve product - ProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Product not found'], 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $produit = Produit::findOrFail($id);
            $produit->fill($request->except('images'));
            $produit->save();

            // Traiter les images si présentes
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    $destinationPath = public_path('imagesP');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($destinationPath, $filename);

                    $produit->images()->create([
                        'chemin' => 'imagesP/' . $filename,
                    ]);
                }
            }

            return response()->json($produit->load('images'), 200);
        } catch (\Exception $e) {
            Log::error('Error updating produit - ProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update produit'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $produit = Produit::findOrFail($id);
            $produit->images()->each(function ($image) {
                $imagePath = public_path($image->chemin);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $image->delete();
            });
            $produit->delete();
            return response()->json(['message' => 'Product deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete product - ProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete product'], 500);
        }
    }
}
