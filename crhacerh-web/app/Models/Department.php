<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    // Un département a plusieurs employés
    public function employees()
    {
        return $this->hasMany(Employe::class);
    }
}
