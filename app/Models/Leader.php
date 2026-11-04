<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leader extends Model
{
    use HasFactory;

    protected $table = 'leaders';

    protected $fillable = [
        'badge_id',
        'nama',
        'status',
    ];

    public function rosters(): HasMany
    {
        return $this->hasMany(Roster::class);
    }
}
