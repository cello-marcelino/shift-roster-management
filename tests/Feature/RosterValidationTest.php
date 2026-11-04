<?php

namespace Tests\Feature;

use App\Models\Line;
use App\Models\Manpower;
use App\Models\Station;
use App\Services\RosterValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_detects_duplicate_badge_in_different_shifts(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 10, 'pola_kerja' => '5_days_2_shift']);
        $station1 = Station::create(['line_id' => $line->id, 'nama' => 'Crimping', 'level' => 'Basic']);
        $station2 = Station::create(['line_id' => $line->id, 'nama' => 'Stripping', 'level' => 'Basic']);

        $mp = Manpower::create([
            'badge_id' => '1001',
            'nama' => 'BUDI',
            'station_utama_1' => 'Crimping',
            'status' => 'aktif',
        ]);

        $service = app(RosterValidationService::class);

        $entries = [
            ['shift' => 1, 'station_id' => $station1->id, 'manpower_id' => $mp->id],
            ['shift' => 2, 'station_id' => $station2->id, 'manpower_id' => $mp->id], // Duplikasi Shift!
        ];

        $result = $service->validateSchedule($line->id, '2026-10-26', $entries);

        $this->assertNotEmpty($result['duplicate_warnings']);
        $this->assertEquals('internal_shift_conflict', $result['duplicate_warnings'][0]['type']);
    }

    public function test_it_allows_one_operator_handling_multiple_stations_in_same_shift(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 10, 'pola_kerja' => '5_days_2_shift']);
        $station1 = Station::create(['line_id' => $line->id, 'nama' => 'Crimping', 'level' => 'Basic']);
        $station2 = Station::create(['line_id' => $line->id, 'nama' => 'Stripping', 'level' => 'Basic']);

        $mp = Manpower::create([
            'badge_id' => '1001',
            'nama' => 'BUDI',
            'station_utama_1' => 'Crimping',
            'station_utama_2' => 'Stripping',
            'status' => 'aktif',
        ]);

        $service = app(RosterValidationService::class);

        // 1 operator memegang 2 station dalam Shift 1
        $entries = [
            ['shift' => 1, 'station_id' => $station1->id, 'manpower_id' => $mp->id],
            ['shift' => 1, 'station_id' => $station2->id, 'manpower_id' => $mp->id],
        ];

        $result = $service->validateSchedule($line->id, '2026-10-26', $entries);

        // Tidak boleh ada duplicate warning!
        $this->assertEmpty($result['duplicate_warnings']);
    }

    public function test_it_allows_two_operators_running_the_same_station(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 10, 'pola_kerja' => '5_days_2_shift']);
        $station = Station::create(['line_id' => $line->id, 'nama' => 'Crimping', 'level' => 'Basic']);

        $mp1 = Manpower::create(['badge_id' => '1001', 'nama' => 'BUDI', 'station_utama_1' => 'Crimping', 'status' => 'aktif']);
        $mp2 = Manpower::create(['badge_id' => '1002', 'nama' => 'SITI', 'station_utama_1' => 'Crimping', 'status' => 'aktif']);

        $service = app(RosterValidationService::class);

        // 2 operator menjalankan station yang sama di Shift 1
        $entries = [
            ['shift' => 1, 'station_id' => $station->id, 'manpower_id' => $mp1->id],
            ['shift' => 1, 'station_id' => $station->id, 'manpower_id' => $mp2->id],
        ];

        $result = $service->validateSchedule($line->id, '2026-10-26', $entries);

        // Tidak boleh ada duplicate warning!
        $this->assertEmpty($result['duplicate_warnings']);
    }

    public function test_it_detects_skill_level_warning_for_trained_station(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 10, 'pola_kerja' => '5_days_2_shift']);
        $stationTrained = Station::create(['line_id' => $line->id, 'nama' => 'Hipot testing', 'level' => 'Trained']);

        $mp = Manpower::create([
            'badge_id' => '1002',
            'nama' => 'SITI',
            'station_utama_1' => 'Crimping',
            'status' => 'aktif',
        ]);

        $service = app(RosterValidationService::class);

        $entries = [
            ['shift' => 1, 'station_id' => $stationTrained->id, 'manpower_id' => $mp->id],
        ];

        $result = $service->validateSchedule($line->id, '2026-10-26', $entries);

        $this->assertNotEmpty($result['skill_warnings']);
        $this->assertEquals('warning', $result['skill_warnings'][0]['type']);
    }

    public function test_it_warns_when_manpower_is_below_standard(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 5, 'pola_kerja' => '5_days_2_shift']);
        $station = Station::create(['line_id' => $line->id, 'nama' => 'Crimping', 'level' => 'Basic']);

        $mp = Manpower::create(['badge_id' => '1003', 'nama' => 'JOKO', 'station_utama_1' => 'Crimping', 'status' => 'aktif']);

        $service = app(RosterValidationService::class);

        $entries = [
            ['shift' => 1, 'station_id' => $station->id, 'manpower_id' => $mp->id],
        ];

        $result = $service->validateSchedule($line->id, '2026-10-26', $entries);

        $this->assertNotEmpty($result['mp_warnings']);
        $this->assertEquals('Sedang', $result['mp_warnings'][0]['severity']);
        $this->assertEquals('warning', $result['mp_warnings'][0]['severity_level']);
        $this->assertStringContainsString('Shift 1', $result['mp_warnings'][0]['reference']);
    }

    public function test_it_does_not_warn_when_draft_is_completely_empty(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 15, 'pola_kerja' => '5_days_2_shift']);
        $service = app(RosterValidationService::class);

        $result = $service->validateSchedule($line->id, '2026-10-26', []);

        $this->assertEmpty($result['mp_warnings']);
        $this->assertEmpty($result['duplicate_warnings']);
        $this->assertEmpty($result['skill_warnings']);
    }

    public function test_warnings_include_severity_and_reference_metadata(): void
    {
        $line = Line::create(['nama' => 'EV10', 'mp_standard' => 5, 'pola_kerja' => '5_days_2_shift']);
        $stationTrained = Station::create(['line_id' => $line->id, 'nama' => 'Hipot testing', 'level' => 'Trained']);

        $mp = Manpower::create([
            'badge_id' => '1004',
            'nama' => 'RATNA',
            'station_utama_1' => 'Packaging',
            'status' => 'aktif',
        ]);

        $service = app(RosterValidationService::class);

        $entries = [
            ['shift' => 1, 'station_id' => $stationTrained->id, 'manpower_id' => $mp->id],
        ];

        $result = $service->validateSchedule($line->id, '2026-10-26', $entries);

        // Cek metadata pada skill_warnings
        $this->assertNotEmpty($result['skill_warnings']);
        $this->assertEquals('Sedang', $result['skill_warnings'][0]['severity']);
        $this->assertEquals('1004', $result['skill_warnings'][0]['badge_id']);
        $this->assertEquals('RATNA', $result['skill_warnings'][0]['operator_nama']);
        $this->assertStringContainsString('Hipot testing (Trained)', $result['skill_warnings'][0]['reference']);
    }
}
