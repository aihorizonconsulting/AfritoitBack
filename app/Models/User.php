<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotificationCustom;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema(
 *     schema="Utilisateurs",
 *     required={"idU", "nomU", "emailU"},
 *     @OA\Property(
 *         property="idU",
 *         type="integer",
 *         format="int64",
 *         description="Identifiant unique de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="nomU",
 *         type="string",
 *         description="Nom de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="prenomU",
 *         type="string",
 *         description="Prénom de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="emailU",
 *         type="string",
 *         format="email",
 *         description="Adresse email de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="telphoneU",
 *         type="string",
 *         description="Numéro de téléphone de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="profilU",
 *         type="string",
 *         description="Profil de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="statutU",
 *         type="string",
 *         description="Statut de l'utilisateur"
 *     ),
 *     @OA\Property(
 *         property="urlPhotoU",
 *         type="string",
 *         description="URL de la photo de l'utilisateur"
 *     ),
 * )
 */

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    public $table = 'utilisateurs';
    protected $primaryKey = 'idU';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'idU',
        'nomU',
        'emailU',
        'prenomU',
        'telphoneU',
        'profilU',
        'statutU',
        'urlPhotoU',
        'email_verified_at'
    ];
    protected $keyType = 'int';
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'mdpU',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getEmailForPasswordReset()
    {
        return $this->emailU;
    }
    public function logements()
    {
        return $this->hasMany('App\Models\Logement', 'idU', 'idU');
    }
    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation', 'client_id', 'idU');
    }
    public function favoris()
    {
        // On ajoute ->withPivot('id') pour récupérer aussi l'id du favori (clé primaire de la table pivot)
        return $this->belongsToMany('App\Models\Logement', 'favoris', 'client_id', 'logement_id')
            ->withPivot('id', 'status');
    }
    public function getAuthPassword()
    {
        return $this->mdpU; // Return the password for authentication
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function getEmailForVerification()
    {
        return $this->emailU;
    }
    public function routeNotificationForMail()
    {
        return $this->emailU;
    }


    public function getRouteKey()
    {
        return $this->getKey();
    }
    public function getRouteKeyName()
    {
        return 'idU';
    }


    public function getAuthIdentifier()
    {
        return $this->{$this->getKeyName()};
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotificationCustom($this));
    }
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    public function produits()
    {
        return $this->hasMany(Produit::class, 'utilisateur_id');
    }
}
