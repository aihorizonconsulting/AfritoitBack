<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UtilisateurController extends Controller
{
    /**
     * Affiche la liste des utilisateurs.
     *
     * @OA\Get(
     *     path="/api/utilisateurs",
     *     summary="Récupérer tous les utilisateurs",
     *     tags={"Utilisateurs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs",
     *         @OA\JsonContent(ref="#/components/schemas/Utilisateurs")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Impossible de récupérer les utilisateurs"
     *     ),
     * )
     */
    public function index()
    {
        try {
            $utilisateurs = User::with('logements.images')->get();
            return response()->json($utilisateurs);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve users - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'Unable to fetch users'],
                500
            );
        }
    }
    public function countUtilisateurs()
    {
        try {
            $count = User::count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Failed to count users - UtilisateurController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to count users'], 500);
        }
    }
    /**
     * Affiche un utilisateur spécifique.
     *
     * @OA\Get(
     *     path="/api/utilisateurs/{id}",
     *     summary="Récupérer un utilisateur par ID",
     *     tags={"Utilisateurs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur trouvé",
     *         @OA\JsonContent(ref="#/components/schemas/Utilisateurs")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $utilisateur = User::with('logements.images')->findOrFail($id);
            return response()->json($utilisateur);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'User not found'],
                404
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $utilisateur = new User();
            $utilisateur->fill($request->all());
            $utilisateur->mdpU = bcrypt($request->mdpU);
            $utilisateur->save();
            return response()->json($utilisateur, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create user - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'Unable to create user'],
                500
            );
        }
    }

    /**
     * Met à jour un utilisateur existant.
     *
     * @OA\Put(
     *     path="/api/utilisateurs/{id}",
     *     summary="Mettre à jour un utilisateur",
     *     tags={"Utilisateurs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Utilisateurs")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur mis à jour"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Impossible de mettre à jour l'utilisateur"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $utilisateur = User::findOrFail($id);
            $utilisateur->fill($request->all());
            if ($request->has('mdpU')) {
                $utilisateur->mdpU = bcrypt($request->mdpU);
            }
            if ($request->hasFile('urlPhotoU')) {
                $file = $request->file('urlPhotoU');
                $prefix = 'profil_' . time() . '_';
                $filename = $prefix . $file->getClientOriginalName();
                $path = $file->storeAs('imgsProfils', $filename, 'public');
                $utilisateur->urlPhotoU = $path;
            } elseif ($request->filled('urlPhotoU')) {
                // Si c'est une URL
                $utilisateur->urlPhotoU = $request->urlPhotoU;
            }
            $utilisateur->update();
            return response()->json($utilisateur);
        } catch (\Exception $e) {
            Log::error('Failed to update user - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'Unable to update user'],
                500
            );
        }
    }

    /**
     * Supprime un utilisateur.
     *
     * @OA\Delete(
     *     path="/api/utilisateurs/{id}",
     *     summary="Supprimer un utilisateur",
     *     tags={"Utilisateurs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Utilisateur supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Impossible de supprimer l'utilisateur"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $utilisateur = User::findOrFail($id);
            $utilisateur->delete();
            return response()->json(['message' => 'User deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete user - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'Unable to delete user'],
                500
            );
        }
    }

     public function deleteUserByEmail(Request $request)
    {
        try {
            $email = $request->input('emailU');
            $utilisateur = User::where('emailU', $email)->firstOrFail();
            $utilisateur->delete();
            return response()->json(['message' => 'User deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete user by email - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'Unable to delete user by email'],
                500
            );
        }
    }
    public function checkIfEmailIsVerified(Request $request)
    {
        try {
            $email = $request->input('emailU');
            $utilisateur = User::where('emailU', $email)->first();
            if ($utilisateur && $utilisateur->email_verified_at) {
                return response()->json(['verified' => true]);
            }
            return response()->json(['verified' => false]);
        } catch (\Exception $e) {
            Log::error('Failed to check email verification - UtilisateurController : ' . $e->getMessage());
            return response()->json(
                ['error' => 'Unable to check email verification'],
                500
            );
        }
    }
}