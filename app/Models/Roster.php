<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roster extends Model
{
    use HasFactory;

    protected $table = 'rosters';

    protected $fillable = [
        'line_id',
        'leader_id',
        'minggu_mulai',
        'minggu_selesai',
        'status',
    ];

    protected $casts = [
        'minggu_mulai' => 'date:Y-m-d',
        'minggu_selesai' => 'date:Y-m-d',
    ];

    public function line(): BelongsTo
    {
        return $this->belongsTo(Line::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Leader::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RosterEntry::class);
    }
}
