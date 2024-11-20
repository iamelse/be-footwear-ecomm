<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoeImage extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function shoe(): BelongsTo
    {
        return $this->belongsTo(Shoe::class);
    }
}
