<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $reservations = Reservation::all();
            return response()->json($reservations, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve reservations - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve reservations'], 500);
        }
    }

    // public function store(Request $request)
    // {
    //     try {
    //         // Validation minimale
    //         $request->validate([
    //             'logement_id' => 'required|exists:logements,idL',
    //             'client_id' => 'required|exists:utilisateurs,idU',
    //             'dateE' => 'nullable|date',
    //             'dateS' => 'nullable|date|after_or_equal:dateE',
    //         ]);

    //         // Récupérer le logement
    //         $logement = \App\Models\Logement::findOrFail($request->logement_id);

    //         // Déduire le type de réservation et la caution
    //         $typeR = $logement->typePeriode === 'mensuelle' ? 'mensuelle' : 'journaliere';
    //         $caution = $typeR === 'mensuelle' ? ($logement->coutLoyer * 2 ?? 0) : null;

    //         // Calcul du montant total si dates fournies
    //         $montantR = $logement->coutLoyer;
    //         if ($request->filled('dateE') && $request->filled('dateS')) {
    //             $dateE = \Carbon\Carbon::parse($request->dateE);
    //             $dateS = \Carbon\Carbon::parse($request->dateS);
    //             $days = $dateE->diffInDays($dateS) ?: 1; // au moins 1 jour
    //             $montantR = $logement->coutLoyer * $days;
    //         }

    //         // Création de la réservation
    //         $reservation = Reservation::create([
    //             'logement_id' => $logement->idL,
    //             'client_id' => $request->client_id,
    //             'dateE' => $request->dateE,
    //             'dateS' => $request->dateS,
    //             'montantR' => $montantR,
    //             'typeR' => $typeR,
    //             'statutR' => 'en attente',
    //             'caution' => $caution,
    //         ]);

    //         return response()->json($reservation, 201);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to create reservation - ReservationController : ' . $e->getMessage());
    //         return response()->json(['error' => 'Failed to create reservation'], 500);
    //     }
    // }
    public function store(Request $request)
    {
        try {
            // Validation minimale
            $request->validate([
                'logement_id' => 'required|exists:logements,idL',
                'client_id' => 'required|exists:utilisateurs,idU',
                'dateE' => 'nullable|date',
                'dateS' => 'nullable|date|after_or_equal:dateE',
            ]);

            // Récupérer le logement
            $logement = \App\Models\Logement::findOrFail($request->logement_id);

            $typeR = $logement->typePeriode === 'mensuelle' ? 'mensuelle' : 'journaliere';

            $montantR = $logement->coutLoyer;
            // Création de la réservation
            $reservation = Reservation::create([
                'logement_id' => $logement->idL,
                'client_id' => $request->client_id,
                'dateE' => $request->dateE,
                'dateS' => $request->dateS,
                'montantR' => $montantR,
                'typeR' => $typeR,
                'statutR' => 'en attente',
            ]);

            return response()->json($reservation, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create reservation - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create reservation'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            return response()->json($reservation, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve reservation - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => "Erreur lors de la recuperation de la reservation"], 404);
        }
    }
    public function getUserReservation()
    {
        try {
            $user = auth()->user();
            $reservations = $user->reservations()->with('logement.images')->get();
            return response()->json($reservations, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user reservations - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la recuperation'], 500);
        }
    }
    public function getLogementReservations($logementId)
    {
        try {
            $reservations = Reservation::where('logement_id', $logementId)->with('client')->get();
            return response()->json($reservations, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve logement reservations - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => "Erreur lors de la recuperation des logements"], 500);
        }
    }
    /**
     * Get reservations for the owner of the logement.
     */
    // This method retrieves all reservations for the logged-in user's logements.
    // It assumes that the user is a property owner and has logements associated with them.
    // It returns the reservations along with the client information.
    public function getProprietaireReservations()
    {
        try {
            $user = auth()->user();
            $logements = $user->logements()->pluck('idL');
            $reservations = Reservation::whereIn('logement_id', $logements)->with('client')->get();
            return response()->json($reservations, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve owner reservations - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la recuperation des reservations'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            // Mettre à jour la réservation
            $reservation->update($request->all());

            // Si la réservation vient d'être acceptée(par le propriétaire)
            // if (isset($request->statutR) && $request->statutR === 'reservee') {
            //     // Rejeter toutes les autres réservations pour ce logement, sauf celle-ci
            //     Reservation::where('logement_id', $reservation->logement_id)
            //         ->where('id', '!=', $reservation->id)
            //         ->where('statutR', '!=', 'rejetee')
            //         ->update(['statutR' => 'rejetee']);
            //         // Mettre le logement à indisponible
            //        $logement = \App\Models\Logement::find($reservation->logement_id);
            //        if ($logement) {
            //            $logement->statutL = 'indisponible';
            //            $logement->save();
            //        }
            // }
            return response()->json($reservation, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update reservation - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update reservation'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            $reservation->delete();
            return response()->json(['message' => 'Reservation deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete reservation - ReservationController : ' . $e->getMessage());
            return response()->json(['error' => "erreur lors de la suppression du logement"], 500);
        }
    }
}
