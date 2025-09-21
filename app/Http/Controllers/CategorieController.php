<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Categorie::all();
            return response()->json($categories, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve categories - CategorieController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve categories'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'nomC' => 'required|string|max:255',
                'codeC' => 'required|string',
            ]);

            $category = Categorie::create($validatedData);
            return response()->json($category, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create category - CategorieController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create category'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = Categorie::findOrFail($id);
            return response()->json($category, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve category - CategorieController : ' . $e->getMessage());
            return response()->json(['error' => 'Category not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validatedData = $request->validate([
                'nomC' => 'sometimes|required|string|max:255',
                'codeC' => 'sometimes|required|string',
            ]);

            $category = Categorie::findOrFail($id);
            $category->update($validatedData);
            return response()->json($category, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update category - CategorieController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update category'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = Categorie::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete category - CategorieController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete category'], 500);
        }
    }
}
