<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manpower extends Model
{
    use HasFactory;

    protected $table = 'manpowers';

    protected $fillable = [
        'badge_id',
        'nama',
        'station_utama_1',
        'station_utama_2',
        'station_utama_3',
        'status',
    ];

    /**
     * Relasi ke entri roster
     */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(RosterEntry::class);
    }

    /**
     * Memeriksa apakah operator relevan dengan nama station tertentu
     * Sesuai PRD 5.4: Operator relevan jika salah satu station utamanya sama dengan station tersebut.
     */
    public function isRelevantToStation(string $stationName): bool
    {
        $normalized = strtolower(trim($stationName));
        
        $preferences = array_filter([
            strtolower(trim((string) $this->station_utama_1)),
            strtolower(trim((string) $this->station_utama_2)),
            strtolower(trim((string) $this->station_utama_3)),
        ]);

        return in_array($normalized, $preferences, true);
    }

    /**
     * Mendapatkan daftar pilihan station utama dalam bentuk array
     */
    public function getStationPreferencesAttribute(): array
    {
        return array_values(array_filter([
            $this->station_utama_1,
            $this->station_utama_2,
            $this->station_utama_3,
        ]));
    }
}
