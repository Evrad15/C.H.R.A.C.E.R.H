<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'type', 'source', 'timestamp', 
        'latitude', 'longitude', 'confidence_score', 'anomaly_detected', 'status'
    ];

    // Un pointage appartient à un employé
    public function employee()
    {
        return $this->belongsTo(Employe::class);
    }

    // Alias pour la compatibilité avec l'ancien code
    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employee_id');
    }
}
