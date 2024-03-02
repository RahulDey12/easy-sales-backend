<?php

namespace App\Models;

use App\Enums\CallerGender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caller extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'gender' => CallerGender::class,
    ];

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public static function getRandom(): static
    {
        return static::query()->inRandomOrder()->first();
    }
}
