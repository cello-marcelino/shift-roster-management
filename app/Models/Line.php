<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Line extends Model
{
    use HasFactory;

    protected $table = 'lines';

    protected $fillable = [
        'nama',
        'mp_standard',
        'pola_kerja',
        'jam_shift',
        'status',
    ];

    protected $casts = [
        'jam_shift' => 'array',
        'mp_standard' => 'integer',
    ];

    /**
     * Stasiun-stasiun kerja milik line ini
     */
    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }

    /**
     * Jadwal roster untuk line ini
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(Roster::class);
    }

    /**
     * Jumlah shift berdasarkan pola kerja (6 hari = 3 shift, 5 hari = 2 shift)
     */
    public function getShiftCountAttribute(): int
    {
        if ($this->pola_kerja === '6_days_3_shift') {
            return 3;
        }
        if ($this->pola_kerja === '5_days_1_shift') {
            return 1;
        }
        return 2;
    }

    /**
     * Label teks pola kerja yang mudah dibaca
     */
    public function getPolaKerjaLabelAttribute(): string
    {
        return match ($this->pola_kerja) {
            '6_days_3_shift' => '6 Hari (3 Shift)',
            '6_days_2_shift' => '6 Hari (2 Shift)',
            '5_days_1_shift' => '5 Hari (1 Shift)',
            default => '5 Hari (2 Shift)',
        };
    }

    /**
     * Jam shift: acuan waktu shift sesuai jumlah shift (bebas dari istilah pagi/sore/malam)
     */
    public function getEffectiveShiftHoursAttribute(): array
    {
        $is3Shift = ($this->pola_kerja === '6_days_3_shift');
        $shiftCount = $this->shift_count;

        $defaultHours = $is3Shift ? [
            1 => '07.00 - 15.00',
            2 => '15.00 - 23.00',
            3 => '23.00 - 07.00',
        ] : [
            1 => '07.00 - 16.00',
            2 => '16.00 - 01.00',
        ];

        $result = [];
        for ($s = 1; $s <= $shiftCount; $s++) {
            $hours = null;
            if (!empty($this->jam_shift) && isset($this->jam_shift[$s]['hours'])) {
                $hours = $this->jam_shift[$s]['hours'];
            }
            $result[$s] = [
                'label' => "Shift {$s}",
                'hours' => $hours ?: ($defaultHours[$s] ?? '07.00 - 16.00'),
            ];
        }

        return $result;
    }
}
