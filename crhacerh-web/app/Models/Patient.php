<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'numero_dossier',
        'nom_complet',
        'telephone',
        'fingerprint_template',
        'face_embedding',
    ];

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }
}
