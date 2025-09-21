<?php

namespace App\Http\Controllers;

use App\Models\ImageProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $images = ImageProduit::with('produit')->get();
            return response()->json($images, 200);
        }catch (\Exception $e) {
            Log::error('Failed to retrieve images - ImageProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve images'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'produit_id' => 'required|exists:produits,id',
                'chemin' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp,tiff,tif,svg,ico,heic,heif,avif',
            ]);
            $data = $request->except('chemin');
            if ($request->hasFile('chemin')) {
                $destinationPath = public_path('imagesP');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file = $request->file('chemin');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);
                $data['chemin'] = 'imagesP/' . $filename;
            }
            $imageProduit = ImageProduit::create($data);
            return response()->json($imageProduit, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create image - ImageProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create image'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $imageProduit = ImageProduit::findOrFail($id);
            $request->validate([
                'chemin' => 'file|mimes:jpg,jpeg,png,gif,bmp,webp,tiff,tif,svg,ico,heic,heif,avif',
            ]);
            if ($request->hasFile('chemin')) {
                $destinationPath = public_path('imagesP');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file = $request->file('chemin');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);
                $imageProduit->chemin = 'imagesP/' . $filename;
            }
            $imageProduit->save();
            return response()->json($imageProduit, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update image - ImageProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update image'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $imageProduit = ImageProduit::findOrFail($id);
            $imageProduit->delete();
            return response()->json(['message' => 'Image deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete image - ImageProduitController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete image'], 500);
        }
    }
}
