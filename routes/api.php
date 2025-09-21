<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ImageProduitController;
use App\Http\Controllers\LogementController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Autorise 60 requêtes par minute pour les routes API
Route::middleware('throttle:60,1')->group(function () {
   
// Routes non protégées par l'authentification
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);
Route::post('password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::get('checkemail', [UtilisateurController::class, 'checkIfEmailIsVerified']);
Route::middleware(['auth:api'])->get('/me', function () {
    return response()->json(auth('api')->user()->load('logements.images', 'favoris.images', 'reservations','produits.images','produits.categorie'));
});

Route::get('logements/search', 'App\Http\Controllers\LogementController@search');
Route::get('logements/filter', 'App\Http\Controllers\LogementController@filter');
Route::get('logements', [LogementController::class, 'index']);
Route::get('logements/paginate', [LogementController::class, 'getLogementsPaginate']);
Route::get('logements/count', [LogementController::class, 'countLogements']);
Route::get('logements/{id}', [LogementController::class, 'show']);
Route::get('utilisateurs/count', [App\Http\Controllers\UtilisateurController::class, 'countUtilisateurs']);
Route::delete('utilisateurs', [App\Http\Controllers\UtilisateurController::class, 'deleteUserByEmail']);
Route::resource('favoris', 'App\Http\Controllers\FavorisController')->only([
    'index',
    'show',
    'store',
    'update',
    'destroy'
]);

Route::get('imagesp/{id}', [ImageProduitController::class, 'show']);
Route::get('imagesp', [ImageProduitController::class, 'index']);

Route::get('produits', [ProduitController::class, 'index']);
Route::get('produits/{id}', [ProduitController::class, 'show']);

Route::get('categories', [CategorieController::class, 'index']);
Route::get('categories/{id}', [CategorieController::class, 'show']);

// Routes protégées par le middleware de rôle
Route::middleware(['auth:api', 'role:ruetartsinmimda'])->group(function () {
       Route::resource('utilisateurs', 'App\Http\Controllers\UtilisateurController')->only([
        'index',
        'store',
        'destroy'
    ]);
});

// Routes protégées par l'authentification
Route::middleware('auth:api')->group(function () {
    Route::post('/logements/{id}', [LogementController::class, 'update']);
    Route::post('logements', [LogementController::class, 'store']);
    Route::delete('logements/{logement}', [LogementController::class, 'destroy']);
    Route::post('/utilisateurs/{id}', [App\Http\Controllers\UtilisateurController::class, 'update']);
    Route::resource('utilisateurs', 'App\Http\Controllers\UtilisateurController')->only([
        'show',
    ]);

    Route::post('/images/{id}', [ImageController::class, 'update']);
    Route::resource('images', 'App\Http\Controllers\ImageController')->only([
        'index',
        'show',
        'store',
        'destroy'
    ]);
    Route::get('reservations/user', [App\Http\Controllers\ReservationController::class, 'getUserReservation']);
    Route::get('reservations/proprietaire', [App\Http\Controllers\ReservationController::class, 'getProprietaireReservations']);
    Route::resource('reservations', 'App\Http\Controllers\ReservationController')->only([
        'index',
        'show',
        'store',
        'update',
        'destroy'
    ]);
    Route::get('reservations/logements/{logementId}', [App\Http\Controllers\ReservationController::class, 'getLogementReservations']);
    
    Route::get('messages/count', [App\Http\Controllers\MessageController::class, 'count']);
    Route::resource('messages', 'App\Http\Controllers\MessageController')->only([
        'store',
        'update',
        'destroy',
    ]);
    Route::post('messages/bulk-delete', [App\Http\Controllers\MessageController::class, 'bulkDelete']);

     Route::resource('categories', CategorieController::class)->only([
        'update',
        'destroy',
        'store'
    ]);
    Route::resource('produits', ProduitController::class)->only([
        'store',
        'destroy'
    ]);
    Route::resource('imagesp', ImageProduitController::class)->only([
        'store',
        'destroy',
    ]);
    Route::post('imagesp/{id}', [ImageProduitController::class, 'update']);
    Route::post('produits/{id}', [ProduitController::class, 'update']);
});
    Route::get('messages', [App\Http\Controllers\MessageController::class, 'index']);
    Route::get('messages/{id}', [App\Http\Controllers\MessageController::class, 'show']);

});