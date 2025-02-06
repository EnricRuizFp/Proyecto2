<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $table = 'rankings';

    protected $fillable = [
        'game_id',
        'type',
        'wins',
        'losses',
        'draws',
        'points',
        'updated_at',
    ];

    // Relaciones

    public function user()
    {
        return $this->belongsTo(User::class, foreignKey: 'user_id');
    }
}
