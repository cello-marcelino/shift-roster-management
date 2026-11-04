<?php

namespace Database\Seeders;

use App\Models\Leader;
use App\Models\Line;
use App\Models\Manpower;
use App\Models\Station;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Leaders
        $leaderNames = [
            'LDR-001' => 'Mr. Faisal',
            'LDR-002' => 'Mr. Yunaldi',
            'LDR-003' => 'Ms. Meli',
            'LDR-004' => 'Mr. Dayu',
            'LDR-005' => 'Mr. Marlis',
            'LDR-006' => 'Mr. Bob Resdo',
        ];

        $leaderMap = [];
        foreach ($leaderNames as $badge => $name) {
            $leader = Leader::firstOrCreate(
                ['badge_id' => $badge],
                ['nama' => $name, 'status' => 'aktif']
            );
            $leaderMap[strtolower($name)] = $leader;
        }

        // 2. Seed Lines
        $linesConfig = [
            'EV5' => ['mp' => 15, 'pola' => '5_days_2_shift'],
            'EV6' => ['mp' => 15, 'pola' => '5_days_2_shift'],
            'EV7' => ['mp' => 15, 'pola' => '5_days_2_shift'],
            'EV8' => ['mp' => 15, 'pola' => '5_days_2_shift'],
            'EV10' => ['mp' => 15, 'pola' => '5_days_2_shift'],
            'EV11' => ['mp' => 15, 'pola' => '5_days_2_shift'],
            'WEBASTO' => ['mp' => 18, 'pola' => '6_days_3_shift'],
            'NTC' => ['mp' => 12, 'pola' => '5_days_2_shift'],
            'WELLBOX' => ['mp' => 10, 'pola' => '5_days_2_shift'],
        ];

        $initialStations = [
            'VIFG' => 'Basic',
            'Packaging' => 'Basic',
            'Molding' => 'Basic-Trained',
            'Potting' => 'Basic-Trained',
            'Hipot testing' => 'Trained',
            'Crimping' => 'Basic',
            'Stripping' => 'Basic',
            'Ultrasonic welding' => 'Trained',
            'Laser marking' => 'Basic-Trained',
            'PCBA testing' => 'Trained',
            'Auto-solder' => 'Trained',
            'Manual solder' => 'Basic-Trained',
            'Seal ring' => 'Basic',
            'Insert housing' => 'Basic',
        ];

        $stationNamesList = array_keys($initialStations);
        $lineModels = [];

        foreach ($linesConfig as $lineName => $cfg) {
            $line = Line::firstOrCreate(
                ['nama' => $lineName],
                [
                    'mp_standard' => $cfg['mp'],
                    'pola_kerja' => $cfg['pola'],
                    'status' => 'aktif',
                    'jam_shift' => $cfg['pola'] === '6_days_3_shift' ? [
                        1 => ['label' => 'Shift 1', 'hours' => '07.00 - 15.00'],
                        2 => ['label' => 'Shift 2', 'hours' => '15.00 - 23.00'],
                        3 => ['label' => 'Shift 3', 'hours' => '23.00 - 07.00'],
                    ] : [
                        1 => ['label' => 'Shift 1', 'hours' => '07.00 - 16.00'],
                        2 => ['label' => 'Shift 2', 'hours' => '16.00 - 01.00'],
                    ],
                ]
            );

            $lineModels[$lineName] = $line;

            // Pastikan setiap line memiliki 14 stasiun awal (PRD 4.4)
            foreach ($initialStations as $stName => $stLevel) {
                Station::firstOrCreate(
                    [
                        'line_id' => $line->id,
                        'nama' => $stName,
                    ],
                    [
                        'level' => $stLevel,
                        'cycle_time' => '45s',
                    ]
                );
            }
        }

        // 3. Impor Manpower dari manpower.json
        $jsonPath = base_path('manpower.json');
        if (File::exists($jsonPath)) {
            $rawData = json_decode(File::get($jsonPath), true);

            if (is_array($rawData)) {
                $stationCount = count($stationNamesList);
                $index = 0;

                foreach ($rawData as $item) {
                    $badge = trim((string) ($item['badge'] ?? ''));
                    $nama = trim((string) ($item['nama'] ?? ''));

                    if ($badge === '' || $nama === '') {
                        continue;
                    }

                    $lines = $item['lines'] ?? [];
                    $shifts = $item['shifts'] ?? [];

                    $isCuti = in_array('CUTI', $lines, true) || in_array('CUTI', $shifts, true);

                    // Assign stasiun utama secara bervariasi untuk pengujian relevansi station
                    $st1 = $stationNamesList[$index % $stationCount];
                    $st2 = $stationNamesList[($index + 2) % $stationCount];
                    $st3 = $stationNamesList[($index + 5) % $stationCount];

                    Manpower::updateOrCreate(
                        ['badge_id' => $badge],
                        [
                            'nama' => strtoupper($nama),
                            'station_utama_1' => $st1,
                            'station_utama_2' => $st2,
                            'station_utama_3' => $st3,
                            'status' => $isCuti ? 'cuti' : 'aktif',
                        ]
                    );

                    $index++;
                }
            }
        }
    }
}
