<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favoris extends Model
{
    use HasFactory;
    protected $table = 'favoris';
    protected $guarded = [];
    public function utilisateur()
    {
        return $this->belongsTo('App\Models\User', 'client_id');
    }
    public function logement(){
        return $this->belongsTo('App\Models\Logement', 'logement_id');
    }
}
