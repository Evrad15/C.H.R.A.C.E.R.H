<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MlPrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'prediction_type', 'confidence', 'explanation'
    ];

    // La prédiction concerne un employé
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
