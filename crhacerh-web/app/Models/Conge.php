<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conge extends Model
{
    use HasFactory;

    protected $fillable = [
        'employe_id',
        'type',
        'date_debut',
        'date_fin',
        'motif',
        'statut',
        'commentaire_rh',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}
