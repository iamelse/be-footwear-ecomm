<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
