<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Logement",
 *     type="object",
 *     title="Logement",
 *     description="Modèle de logement",
 *     @OA\Property(property="idL", type="integer", example=1),
 *     @OA\Property(property="idU", type="integer", example=2),
 *     @OA\Property(property="libelleL", type="string", example="Villa Baobab"),
 *     @OA\Property(property="adresseL", type="string", example="Dakar, Point E"),
 *     @OA\Property(property="coutLoyer", type="number", format="float", example=150000),
 *     @OA\Property(property="surface", type="float", example="200"),
 *     @OA\Property(property="statutL", type="string", example="disponible"),
 *     @OA\Property(property="typePeriode", type="string", example="Mensuel"),
 *     @OA\Property(property="nombrePieces", type="integer", example=3),
 *     @OA\Property(property="typeLogement", type="string", example="Appartement"),
 *     @OA\Property(property="descriptionL", type="string", example="Très bel appartement spacieux"),
 * )
 */
class Logement extends Model
{
    public $table = 'logements'; 
    protected $primaryKey = 'idL';
    protected $fillable = [
        'libelleL',
        'descriptionL',
        'adresseL',
        'coutLoyer',
        'surface',
        'nombrePieces',
        'typeLogement',
        'typePeriode', 
        'typeL',
        'statutL',
        'dateDisponibilite',
        'idU', // Foreign key to the user who owns the logement
    ];
    use HasFactory;
    public function proprietaire()
    {
        return $this->belongsTo('App\Models\User', 'idU', 'idU'); 
    }
    public function images()
    {
        return $this->hasMany('App\Models\Image', 'logement_id', 'idL');
    }
    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation', 'logement_id', 'idL');
    }
    public function scopeAvailable($query)
    {
        return $query->where('statutL', 'disponible');
    }
    public function scopeUnavailable($query)
    {
        return $query->where('statutL', '!=', 'disponible');
    }
    
}
