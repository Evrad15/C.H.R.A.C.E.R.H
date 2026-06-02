<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasFactory;

    // Définissez les champs que vous souhaitez rendre modifiables
    protected $fillable = ['titre', 'contenu', 'date_rapport', 'employe_id'];

    // Si le rapport appartient à un employé
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}