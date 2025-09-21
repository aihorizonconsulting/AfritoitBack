<?php

namespace App\Http\Controllers;

use App\Models\Logement;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class LogementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/logements",
     *     summary="Lister tous les logements",
     *     description="Retourne la liste complète des logements avec leurs propriétaires",
     *     operationId="getLogements",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des logements",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Logement")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function index()
    {
        try {
            $logements = Logement::with('proprietaire', 'images')->get();
            return response()->json($logements);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error('Error fetching logements - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch logements', 500]);
        }
    }

    public function countLogements()
    {
        try {
            $count = Logement::count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error counting logements - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to count logements'], 500);
        }
    }
    public function getLogementsPaginate(Request $request)
    {
        try {
            $limit = $request->input('limit', 10); // Nombre d'éléments par page, par défaut 10
            $logements = Logement::with('proprietaire', 'images')->paginate($limit);
            return response()->json($logements);
        } catch (\Exception $e) {
            Log::error('Error fetching logements paginate - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch logements'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/logements/{idL}",
     *     summary="Afficher un logement spécifique",
     *     description="Retourne les détails d'un logement par son ID",
     *     operationId="getLogementById",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="idL",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Détails du logement",
     *         @OA\JsonContent(ref="#/components/schemas/Logement")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Logement non trouvé"
     *     )
     * )
     */
    public function show($idL)
    {
        try {
            $logement = Logement::with('proprietaire', 'images', 'reservations')->where('idL', $idL)->first(); // Fetch a specific logement by ID
            return response()->json($logement);
        } catch (\Exception $e) {
            Log::error('Error showing logements  - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Logement not found'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logements",
     *     summary="Créer un nouveau logement",
     *     description="Crée un nouveau logement avec les détails fournis",
     *     operationId="createLogement",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Logement")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Logement créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Logement")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'images.*' => 'file|max:51200',
            ], [
                'images.*.max' => 'Chaque image ne doit pas dépasser 50 Mo.',
            ]);

            // Créer le logement
            $logement = new Logement();
            $logement->fill($request->except('images'));
            $logement->save();

            // Traiter les images si présentes
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    $destinationPath = public_path('imagesL');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($destinationPath, $filename);

                    $logement->images()->create([
                        'url' => 'imagesL/' . $filename,
                    ]);
                }
            }

            return response()->json($logement->load('images'), 201);
        } catch (\Exception $e) {
            Log::error('Error creating logement - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to create logement'], 500);
        }
    }
    /**
     * @OA\Put(
     *     path="/api/logements/{id}",
     *     summary="Mettre à jour un logement",
     *     description="Met à jour les détails d'un logement existant",
     *     operationId="updateLogement",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Logement")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logement mis à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Logement")
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Logement non trouvé"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $logement = Logement::findOrFail($id);

            // Valider les champs du logement
            $request->validate([
                'images.*' => 'file|mimes:jpg,png', // Valider chaque nouvelle image ajoutée
            ]);

            // Mettre à jour les infos du logement
            $logement->fill($request->except('images'));
            $logement->save();

            // Ajouter de nouvelles images si présentes
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                if (!is_array($files)) {
                    $files = [$files];
                }
                foreach ($files as $file) {
                    $destinationPath = public_path('imagesL');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($destinationPath, $filename);

                    $logement->images()->create([
                        'url' => 'imagesL/' . $filename,
                    ]);
                }
            }

            return response()->json($logement->load('images'), 200);
        } catch (\Exception $e) {
            Log::error('Error updating logement - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to update logement'], 500);
        }
    }
    /**
     * @OA\Delete(
     *     path="/api/logements/{id}",
     *     summary="Supprimer un logement",
     *     description="Supprime un logement par son ID",
     *     operationId="deleteLogement",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="Logement supprimé avec succès"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Logement non trouvé"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $logement = Logement::findOrFail($id);
            $logement->delete();
            return response()->json(['message' => 'Logement deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting logement - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to delete logement'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/logements/search",
     *     summary="Rechercher des logements",
     *     description="Recherche des logements en fonction de critères spécifiques",
     *     operationId="searchLogements",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="libelleL",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="typeLogement",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="statutL",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="adresseL",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Liste des logements correspondant aux critères de recherche",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Logement"))
     *     )
     *  )
     */
    public function search(Request $request)
    {
        try {
            $fieldsToUpper = ['libelleL', 'typeLogement', 'statutL', 'adresseL'];
            foreach ($fieldsToUpper as $field) {
                if ($request->has($field)) {
                    $request->merge([$field => strtoupper($request->$field)]);
                }
            }
            $query = Logement::query()->with('proprietaire', 'images');

            if ($request->filled('libelleL')) {
                $query->whereRaw('UPPER("libelleL") LIKE ?', ['%' . $request->libelleL . '%']);
            }
            if ($request->filled('typeLogement')) {
                $query->whereRaw('UPPER("typeLogement") = ?', [$request->typeLogement]);
            }
            if ($request->filled('statutL')) {
                $query->whereRaw('UPPER("statutL") = ?', [$request->statutL]);
            }
            if ($request->filled('adresseL')) {
                $query->whereRaw('UPPER("adresseL") LIKE ?', ['%' . $request->adresseL . '%']);
            }

            $logements = $query->get();
            return response()->json($logements);
            $query = Logement::query();

            if ($request->has('libelleL')) {
                $query->where('libelleL', 'like', '%' . strtoupper($request->libelleL) . '%');
            }
            if ($request->has('typeLogement')) {
                $query->where('typeLogement', strtoupper($request->typeLogement));
            }
            if ($request->has('statutL')) {
                $query->where('statutL', $request->statutL);
            }
            if ($request->has('adresseL')) {
                $query->where('adresseL', 'like', '%' . strtoupper($request->adresseL) . '%');
            }

            $logements = $query->get();
            return response()->json($logements);
        } catch (\Exception $e) {
            Log::error('Error searching logements - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to search logements'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/logements/filter",
     *     summary="Filtrer les logements",
     *     description="Filtre les logements en fonction de critères spécifiques",
     *     operationId="filterLogements",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="typeLogement",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="statutL",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="surface_min",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *     @OA\Parameter(
     *         name="surface_max",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="Liste des logements filtrés",
     *        @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Logement"))
     * )
     * )
     */
    public function filter(Request $request)
    {
        try {
            $query = Logement::query();

            if ($request->has('typeLogement')) {
                $query->where('typeLogement', $request->typeLogement);
            }
            if ($request->has('statutL')) {
                $query->where('statutL', $request->statutL);
            }
            if ($request->has('surface_min')) {
                $query->where('surface', '>=', $request->surface_min);
            }
            if ($request->has('surface_max')) {
                $query->where('surface', '<=', $request->surface_max);
            }

            $logements = $query->get();
            return response()->json($logements);
        } catch (\Exception $e) {
            Log::error('Error filtering logements - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to filter logements'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/logements/sort",
     *     summary="Trier les logements",
     *     description="Trie les logements en fonction d'un critère spécifique",
     *     operationId="sortLogements",
     *     tags={"Logements"},
     *     security={{"bearerAuth":{}}},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"libelleL", "coutLoyer", "surface"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *
     *    @OA\Response(
     *        response=200,
     *        description="Liste des logements triés",
     *        @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Logement"))
     * )
     * )
     */

    public function sort(Request $request)
    {
        try {
            $query = Logement::query();

            if ($request->has('sort_by')) {
                $sortBy = $request->sort_by;
                $sortOrder = $request->get('sort_order', 'asc');
                $query->orderBy($sortBy, $sortOrder);
            }

            $logements = $query->get();
            return response()->json($logements);
        } catch (\Exception $e) {
            Log::error('Error sorting logements - LogementController : ' . $e->getMessage());
            return response()->json(['error' => 'Unable to sort logements'], 500);
        }
    }
}
