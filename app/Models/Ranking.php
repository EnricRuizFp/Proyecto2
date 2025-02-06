<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    protected $table = 'rankings';
    
    // Tu clave primaria es 'ranking_id'
    protected $primaryKey = 'ranking_id';
    
    // Desactiva timestamps "clásicos" si no usas created_at, updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'wins',
        'losses',
        'draws',
        'points',
        'updated_at',
    ];

    /**
     * Relación: Un Ranking pertenece a un User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
