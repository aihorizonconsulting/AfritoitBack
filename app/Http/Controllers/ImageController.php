<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $images = Image::all();
            return response()->json($images, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve images - ImageController : ' . $e->getMessage());
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
                'logement_id' => 'required|exists:logements,idL',
                'url' => 'required|file|mimes:jpg,jpeg,png,gif,bmp,webp,tiff,tif,svg,ico,heic,heif,avif',
            ]);
            $data = $request->except('url');
            if ($request->hasFile('url')) {
                $destinationPath = public_path('imagesL');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file = $request->file('url');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);
                $data['url'] = 'imagesL/' . $filename;
            }
            $image = Image::create($data);
            return response()->json($image, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create image - ImageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create image'], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $image = Image::findOrFail($id);
            $request->validate([
                'url' => 'nullable|file|mimes:jpg,png|max:5120',
            ]);
            $data = $request->except('url');
            if ($request->hasFile('url')) {
                $destinationPath = public_path('imagesL');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file = $request->file('url');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);
                $data['url'] = 'imagesL/' . $filename;
                // Supprimer l'ancienne image si elle existe
                $oldPath = public_path($image->url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $image->update($data);
            return response()->json($image, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update image - ImageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update image'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $image = Image::findOrFail($id);
            $image->delete();
            // Supprimer le fichier physique
            $filePath = public_path($image->url);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return response()->json(['message' => 'Image deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete image - ImageController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete image'], 500);
        }
    }
}
