<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barco extends Model
{
    use HasFactory;

    protected $table = 'barcos';

    protected $fillable = [
        'nombre',
        'tamaño',
    ];

    // Relaciones

    public function barcosPosiciones()
    {
        return $this->hasMany(BarcosPosicion::class, 'tipo_id');
    }
}
