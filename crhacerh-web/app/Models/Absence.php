<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'date_start', 'date_end', 'type', 'status', 'approved_by', 'justification_url'
    ];

    // L'absence appartient à un employé
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // L'absence est approuvée par un employé (Manager/RH)
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
