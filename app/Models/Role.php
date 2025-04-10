<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * The permissions that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return parent::permissions();
    }

    /**
     * Get the formatted date attributes.
     *
     * @param string $date
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    /**
     * Get the formatted date attributes.
     *
     * @param string $date
     * @return string
     */
    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
    }
}
