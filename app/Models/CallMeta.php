<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallMeta extends Model
{
    use HasFactory;

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }
}
