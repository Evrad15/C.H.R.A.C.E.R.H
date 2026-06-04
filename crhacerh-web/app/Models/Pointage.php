<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pointage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'type', 'source', 'timestamp', 
        'latitude', 'longitude', 'confidence_score', 'anomaly_detected'
    ];

    // Un pointage appartient à un employé
    public function employee()
    {
        return $this->belongsTo(Employe::class);
    }
}
