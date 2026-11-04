<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterEntry extends Model
{
    use HasFactory;

    protected $table = 'roster_entries';

    protected $fillable = [
        'roster_id',
        'hari',
        'shift',
        'station_id',
        'manpower_id',
        'start_shift',
        'end_shift',
    ];

    public function roster(): BelongsTo
    {
        return $this->belongsTo(Roster::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(Manpower::class);
    }
}
