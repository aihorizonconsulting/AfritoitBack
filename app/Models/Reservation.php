<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservations';
    protected $guarded = [];
    public function logement()
    {
        return $this->belongsTo(Logement::class, 'logement_id', 'idL');
    }
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'idU');
    }
}
