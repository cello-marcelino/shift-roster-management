<?php

namespace Tests\Feature;

use App\Livewire\MasterLeader;
use App\Livewire\MasterLine;
use App\Livewire\MasterManpower;
use App\Livewire\RosterSchedule;
use App\Models\Leader;
use App\Models\Line;
use App\Models\Manpower;
use App\Models\Roster;
use App\Models\RosterEntry;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperationalRosterTest extends TestCase
{
    use RefreshDatabase;

    protected Line $line;
    protected Leader $leader;
    protected Station $station1;
    protected Station $station2;
    protected Manpower $operator1;
    protected Manpower $operator2;
    protected Manpower $operatorCuti;

    protected function setUp(): void
    {
        parent::setUp();

        $this->line = Line::create([
            'nama' => 'EV10',
            'mp_standard' => 2,
            'pola_kerja' => '5_days_2_shift',
            'status' => 'aktif',
        ]);

        $this->station1 = Station::create([
            'line_id' => $this->line->id,
            'nama' => 'Crimping',
            'level' => 'Basic',
        ]);

        $this->station2 = Station::create([
            'line_id' => $this->line->id,
            'nama' => 'Hipot testing',
            'level' => 'Trained',
        ]);

        $this->leader = Leader::create([
            'badge_id' => 'LDR-001',
            'nama' => 'Mr. Faisal',
            'status' => 'aktif',
        ]);

        $this->operator1 = Manpower::create([
            'badge_id' => '678',
            'nama' => 'WARDANA',
            'station_utama_1' => 'Crimping',
            'status' => 'aktif',
        ]);

        $this->operator2 = Manpower::create([
            'badge_id' => '2686',
            'nama' => 'KRISTINA',
            'station_utama_1' => 'Hipot testing',
            'status' => 'aktif',
        ]);

        $this->operatorCuti = Manpower::create([
            'badge_id' => '9999',
            'nama' => 'BUDI CUTI',
            'station_utama_1' => 'Crimping',
            'status' => 'cuti',
        ]);
    }

    public function test_master_manpower_page_renders_and_toggles_cuti(): void
    {
        $response = $this->get('/manpower');
        $response->assertStatus(200);

        Livewire::test(MasterManpower::class)
            ->assertSee('WARDANA')
            ->assertSee('678')
            ->call('toggleStatus', $this->operator1->id);

        $this->assertEquals('cuti', $this->operator1->fresh()->status);
    }

    public function test_master_line_page_renders_and_adds_station(): void
    {
        $response = $this->get('/lines');
        $response->assertStatus(200);

        Livewire::test(MasterLine::class)
            ->set('selectedLineId', $this->line->id)
            ->set('station_nama', 'Ultrasonic welding')
            ->set('station_level', 'Trained')
            ->call('saveStation');

        $this->assertDatabaseHas('stations', [
            'line_id' => $this->line->id,
            'nama' => 'Ultrasonic welding',
            'level' => 'Trained',
        ]);
    }

    public function test_create_line_with_2_shift_cycle_and_shift_hours(): void
    {
        Livewire::test(MasterLine::class)
            ->call('openCreateLineModal')
            ->set('nama', 'LINE_CUSTOM')
            ->set('mp_standard', 8)
            ->set('pola_kerja', '5_days_2_shift')
            ->set('shift1_start', '07.30')
            ->set('shift1_end', '16.30')
            ->set('shift2_start', '16.30')
            ->set('shift2_end', '01.30')
            ->call('saveLine');

        $this->assertDatabaseHas('lines', [
            'nama' => 'LINE_CUSTOM',
            'pola_kerja' => '5_days_2_shift',
            'mp_standard' => 8,
        ]);

        $line = Line::where('nama', 'LINE_CUSTOM')->first();
        $this->assertEquals(2, $line->shift_count);
        $this->assertEquals('5 Hari (2 Shift)', $line->pola_kerja_label);
        $this->assertEquals([
            1 => ['label' => 'Shift 1', 'hours' => '07.30 - 16.30'],
            2 => ['label' => 'Shift 2', 'hours' => '16.30 - 01.30'],
        ], $line->effective_shift_hours);
    }

    public function test_create_line_with_6_days_3_shift_cycle(): void
    {
        Livewire::test(MasterLine::class)
            ->call('openCreateLineModal')
            ->set('nama', 'LINE_3_SHIFT')
            ->set('mp_standard', 18)
            ->set('pola_kerja', '6_days_3_shift')
            ->set('shift1_start', '07.00')
            ->set('shift1_end', '15.00')
            ->set('shift2_start', '15.00')
            ->set('shift2_end', '23.00')
            ->set('shift3_start', '23.00')
            ->set('shift3_end', '07.00')
            ->call('saveLine');

        $this->assertDatabaseHas('lines', [
            'nama' => 'LINE_3_SHIFT',
            'pola_kerja' => '6_days_3_shift',
            'mp_standard' => 18,
        ]);

        $line = Line::where('nama', 'LINE_3_SHIFT')->first();
        $this->assertEquals(3, $line->shift_count);
        $this->assertEquals('6 Hari (3 Shift)', $line->pola_kerja_label);
        $this->assertEquals([
            1 => ['label' => 'Shift 1', 'hours' => '07.00 - 15.00'],
            2 => ['label' => 'Shift 2', 'hours' => '15.00 - 23.00'],
            3 => ['label' => 'Shift 3', 'hours' => '23.00 - 07.00'],
        ], $line->effective_shift_hours);
    }

    public function test_delete_line_removes_line_and_related_data(): void
    {
        $lineToDelete = Line::create([
            'nama' => 'LINE_TEST_DELETE',
            'mp_standard' => 5,
            'pola_kerja' => '5_days_2_shift',
            'status' => 'aktif',
        ]);

        $st = Station::create([
            'line_id' => $lineToDelete->id,
            'nama' => 'Station To Delete',
            'level' => 'Basic',
        ]);

        $roster = Roster::create([
            'line_id' => $lineToDelete->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => '2026-11-02',
            'minggu_selesai' => '2026-11-08',
            'status' => 'draft',
        ]);

        RosterEntry::create([
            'roster_id' => $roster->id,
            'hari' => 1,
            'shift' => 1,
            'station_id' => $st->id,
            'manpower_id' => $this->operator1->id,
        ]);

        Livewire::test(MasterLine::class)
            ->call('deleteLine', $lineToDelete->id);

        $this->assertDatabaseMissing('lines', ['id' => $lineToDelete->id]);
        $this->assertDatabaseMissing('stations', ['id' => $st->id]);
        $this->assertDatabaseMissing('rosters', ['id' => $roster->id]);
    }

    public function test_master_leader_page_renders(): void
    {
        $response = $this->get('/leaders');
        $response->assertStatus(200);
        $response->assertSee('Mr. Faisal');
    }

    public function test_roster_schedule_click_assignment_and_save(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->set('targetShiftForAdd', 1)
            ->call('addOperatorToShift', $this->operator1->id)
            ->call('addStationToOperator', 1, $this->operator1->id, $this->station1->id)
            ->call('addStationToOperator', 1, $this->operator1->id, $this->station2->id) // 1 operator memegang 2 station
            ->call('saveDraft');

        $this->assertDatabaseHas('roster_entries', [
            'shift' => 1,
            'station_id' => $this->station1->id,
            'manpower_id' => $this->operator1->id,
        ]);

        $this->assertDatabaseHas('roster_entries', [
            'shift' => 1,
            'station_id' => $this->station2->id,
            'manpower_id' => $this->operator1->id,
        ]);
    }

    public function test_roster_status_badge_transitions_between_saved_and_dirty_state(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday);

        // Awal muat (sinkron dengan DB) -> status DRAFT dengan aksen kuning (border-amber-500)
        $component->assertSeeHtml('border-amber-500')
            ->assertDontSeeHtml('border-slate-500')
            ->assertSee('DRAFT');
        $this->assertFalse($component->get('hasUnsavedChanges'));

        // Saat ada perubahan (menambah operator) -> status DRAFT dengan aksen abu-abu slate (border-slate-500 animate-pulse)
        $component->call('addOperatorToShift', $this->operator1->id)
            ->assertSeeHtml('border-slate-500')
            ->assertDontSeeHtml('border-amber-500')
            ->assertSee('DRAFT');
        $this->assertTrue($component->get('hasUnsavedChanges'));

        // Saat disimpan -> status kembali ke aksen kuning (border-amber-500)
        $component->call('saveDraft')
            ->assertSeeHtml('border-amber-500')
            ->assertDontSeeHtml('border-slate-500')
            ->assertSee('DRAFT');
        $this->assertFalse($component->get('hasUnsavedChanges'));

        // Saat ditetapkan final -> status menjadi FINAL dengan aksen hijau (border-emerald-600)
        $component->call('setFinal')
            ->assertSeeHtml('border-emerald-600')
            ->assertDontSeeHtml('border-amber-500')
            ->assertDontSeeHtml('border-slate-500')
            ->assertSee('FINAL');
    }

    public function test_pdf_export_downloads_a4_landscape(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $sunday = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $roster = Roster::create([
            'line_id' => $this->line->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => $sunday,
            'status' => 'final',
        ]);

        RosterEntry::create([
            'roster_id' => $roster->id,
            'hari' => 1,
            'shift' => 1,
            'station_id' => $this->station1->id,
            'manpower_id' => $this->operator1->id,
        ]);

        $response = $this->get(route('roster.export.single', $roster->id));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        $renderedView = view('pdf.roster-landscape', [
            'rosters' => [$roster],
            'isSingle' => true,
        ])->render();
        $this->assertStringContainsString('Start - End', $renderedView);
        $this->assertStringNotContainsString('SISTEM PENJADWALAN OPERASIONAL MINGGUAN', $renderedView);
        $this->assertStringNotContainsString('Start - End Shift', $renderedView);
        $this->assertStringContainsString('color: #000000', $renderedView);
        $this->assertStringContainsString('white-space: nowrap', $renderedView);
        $this->assertStringNotContainsString('Jam:', $renderedView);
    }

    public function test_merged_pdf_export_downloads(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $sunday = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $roster = Roster::create([
            'line_id' => $this->line->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => $sunday,
            'status' => 'final',
        ]);

        $response = $this->get(route('roster.export.merged', ['week_start' => $monday]));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_pdf_view_streams_inline_without_download(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $sunday = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $roster = Roster::create([
            'line_id' => $this->line->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => $sunday,
            'status' => 'final',
        ]);

        RosterEntry::create([
            'roster_id' => $roster->id,
            'hari' => 1,
            'shift' => 1,
            'station_id' => $this->station1->id,
            'manpower_id' => $this->operator1->id,
        ]);

        // Uji viewSingle: harus stream inline, bukan attachment
        $response = $this->get(route('roster.view.single', $roster->id));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));

        // Uji viewMerged: harus stream inline
        $mergedResponse = $this->get(route('roster.view.merged', ['week_start' => $monday]));
        $mergedResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $mergedResponse->headers->get('content-type'));
        $this->assertStringContainsString('inline', $mergedResponse->headers->get('content-disposition'));
    }

    public function test_quick_fill_populates_full_roster(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        // Line memiliki 2 station x 2 shift = 4 slot
        // Kita punya operator1 dan operator2 aktif
        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('quickFill', false);

        $shiftAssignments = $component->get('shiftAssignments');
        $this->assertNotEmpty($shiftAssignments[1]);

        // Uji simpan draft setelah quick fill
        $component->call('saveDraft');

        $this->assertDatabaseHas('rosters', [
            'line_id' => $this->line->id,
            'status' => 'draft',
        ]);

        // Uji clear all slots
        $component->call('clearAllSlots');
        $this->assertEmpty($component->get('shiftAssignments')[1]);
    }

    public function test_quick_fill_manpower_only(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        // Panggil quickFillManpowerOnly
        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('quickFillManpowerOnly');

        $shiftAssignments = $component->get('shiftAssignments');
        // Operator harus terisi di shift 1 dan shift 2 (karena mp_standard = 2)
        $this->assertNotEmpty($shiftAssignments[1]);

        // Pastikan stasiun untuk operator adalah array kosong (belum ada stasiun)
        foreach ($shiftAssignments[1] as $mpId => $stationIds) {
            $this->assertIsArray($stationIds);
            $this->assertEmpty($stationIds);
        }

        // Simpan sebagai draft
        $component->call('saveDraft');

        // Pastikan tersimpan di DB dengan station_id NULL
        $firstMpId = array_key_first($shiftAssignments[1]);
        $this->assertDatabaseHas('roster_entries', [
            'shift' => 1,
            'station_id' => null,
            'manpower_id' => $firstMpId,
        ]);

        // Uji reload: ketika di-reload, operator tetap muncul dengan array stasiun kosong
        $reloadComponent = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday);

        $reloadedAssignments = $reloadComponent->get('shiftAssignments');
        $this->assertArrayHasKey($firstMpId, $reloadedAssignments[1]);
        $this->assertEmpty($reloadedAssignments[1][$firstMpId]);

        // Sekarang pasang station untuk operator tersebut
        $reloadComponent->call('addStationToOperator', 1, $firstMpId, $this->station1->id)
            ->call('saveDraft');

        $this->assertDatabaseHas('roster_entries', [
            'shift' => 1,
            'station_id' => $this->station1->id,
            'manpower_id' => $firstMpId,
        ]);
    }

    public function test_master_manpower_pagination_and_navigation(): void
    {
        // Buat 60 manpower dummy untuk memicu pagination dengan banyak halaman
        for ($i = 1; $i <= 60; $i++) {
            Manpower::create([
                'badge_id' => "TEST-{$i}",
                'nama' => "OPERATOR {$i}",
                'status' => 'aktif',
            ]);
        }

        Livewire::test(MasterManpower::class)
            ->assertSee('Showing')
            ->assertSee('list')
            ->set('perPage', 10)
            ->assertSee('1-10')
            ->call('nextPage')
            ->assertSee('11-20')
            ->call('gotoPage', 3)
            ->assertSee('21-30')
            ->assertSee('...') // Left and/or right dots present in window
            ->call('previousPage')
            ->assertSee('11-20')
            ->set('perPage', 25)
            ->assertSee('1-25');
    }

    public function test_landing_page_shows_instructions_when_no_lines_exist(): void
    {
        // Hapus semua line dari database
        Line::query()->delete();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Belum Ada Lini Produksi Terdaftar');
        $response->assertSee('Instruksi Pengisian Data Line:');
        $response->assertSee('Ke Menu Master Line');
    }

    public function test_roster_schedule_delete_current_line(): void
    {
        $line2 = Line::create([
            'nama' => 'EV20',
            'mp_standard' => 3,
            'pola_kerja' => '5_days_2_shift',
            'status' => 'aktif',
        ]);

        Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->call('deleteCurrentLine')
            ->assertSet('selectedLineId', $line2->id)
            ->assertSee("Line '{$this->line->nama}' beserta seluruh stasiun dan riwayat roster terkait berhasil dihapus dari sistem.");

        $this->assertDatabaseMissing('lines', [
            'id' => $this->line->id,
        ]);
        $this->assertDatabaseMissing('stations', [
            'id' => $this->station1->id,
        ]);
    }

    public function test_roster_schedule_quick_edit_line_modal(): void
    {
        Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->call('openEditLineModal')
            ->assertSet('showEditLineModal', true)
            ->assertSet('editLineNama', 'EV10')
            ->set('editLineNama', 'EV10_REVISED')
            ->set('editLineMpStandard', 12)
            ->set('editLinePolaKerja', '6_days_3_shift')
            ->set('editLineShift1Start', '07.00')
            ->set('editLineShift1End', '15.00')
            ->set('editLineShift2Start', '15.00')
            ->set('editLineShift2End', '23.00')
            ->set('editLineShift3Start', '23.00')
            ->set('editLineShift3End', '07.00')
            ->call('saveEditedLine')
            ->assertSet('showEditLineModal', false)
            ->assertSee("Data Line 'EV10_REVISED' berhasil diperbarui.");

        $this->assertDatabaseHas('lines', [
            'id' => $this->line->id,
            'nama' => 'EV10_REVISED',
            'mp_standard' => 12,
            'pola_kerja' => '6_days_3_shift',
        ]);

        $line = Line::find($this->line->id);
        $this->assertEquals(3, $line->shift_count);
        $this->assertEquals('6 Hari (3 Shift)', $line->pola_kerja_label);
        $this->assertCount(3, $line->effective_shift_hours);
    }

    public function test_clear_all_slots_clears_mp_warnings_and_warning_banner(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('addOperatorToShift', $this->operator1->id)
            ->assertSee('set station')
            ->assertDontSee('Belum ada station')
            ->assertSeeHtml('class="w-14 px-1 whitespace-nowrap">Badge</th>')
            ->call('addStationToOperator', 1, $this->operator1->id, $this->station1->id);

        // Setelah dikosongkan, tidak boleh ada peringatan kekurangan MP
        $component->call('clearAllSlots')
            ->assertDontSee('Peringatan MP: Shift 1 terisi 0 operator')
            ->assertDontSee('Peringatan MP: Shift 2 terisi 0 operator');

        $validationResults = $component->get('validationResults');
        $this->assertEmpty($validationResults['mp_warnings']);
        $this->assertEmpty($validationResults['duplicate_warnings']);
        $this->assertEmpty($validationResults['skill_warnings']);
    }

    public function test_minimal_warning_banner_design(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        // Operator 1 memiliki station_utama Crimping, dipasangkan ke station 2 (Hipot testing - Trained)
        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('addOperatorToShift', $this->operator1->id)
            ->call('addStationToOperator', 1, $this->operator1->id, $this->station2->id);

        // Desain minimal menampilkan jumlah stasiun di luar keahlian dan tombol dismiss
        $component->assertSee('stasiun di luar keahlian')
            ->assertDontSee('Peringkat 1 (Kritis)')
            ->assertDontSee('Lihat Rujukan Detail');
    }

    public function test_operator_cannot_be_assigned_to_multiple_shifts_in_same_line(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            // Tambahkan operator 1 ke Shift 1
            ->call('openAddOperatorModal', 1)
            ->call('addOperatorToShift', $this->operator1->id);

        $shiftAssignments = $component->get('shiftAssignments');
        $this->assertArrayHasKey($this->operator1->id, $shiftAssignments[1]);

        // Coba tambahkan operator 1 ke Shift 2
        $component->call('openAddOperatorModal', 2)
            ->call('addOperatorToShift', $this->operator1->id);

        // Harus dicegah: tidak boleh masuk ke Shift 2 dan muncul pesan error
        $shiftAssignmentsAfter = $component->get('shiftAssignments');
        $this->assertArrayNotHasKey($this->operator1->id, $shiftAssignmentsAfter[2] ?? []);
        $this->assertStringContainsString('sudah ditugaskan pada Shift 1', $component->get('errorMessage'));
    }

    public function test_operator_cannot_be_assigned_if_already_scheduled_in_another_line(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        // Buat roster line lain dan masukkan operator 1 ke sana
        $otherLine = Line::create([
            'nama' => 'Line Webasto',
            'deskripsi' => 'Webasto Line',
            'shift_count' => 2,
            'mp_standard' => 5,
            'status' => 'aktif',
        ]);

        $otherRoster = Roster::create([
            'line_id' => $otherLine->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => Carbon::parse($monday)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d'),
            'status' => 'draft',
        ]);

        RosterEntry::create([
            'roster_id' => $otherRoster->id,
            'manpower_id' => $this->operator1->id,
            'station_id' => null,
            'shift' => 1,
        ]);

        // Buka jadwal line pertama dan coba tambahkan operator 1
        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('openAddOperatorModal', 1)
            ->call('addOperatorToShift', $this->operator1->id);

        // Harus dicegah: tidak boleh masuk ke shift dan muncul pesan error
        $shiftAssignments = $component->get('shiftAssignments');
        $this->assertArrayNotHasKey($this->operator1->id, $shiftAssignments[1] ?? []);
        $this->assertStringContainsString('sudah ditugaskan di Line Webasto', $component->get('errorMessage'));
    }

    public function test_table_displays_start_and_end_shift_columns_and_saves_custom_work_hours(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('addOperatorToShift', $this->operator1->id)
            ->assertSee('set station')
            ->assertDontSee('Belum ada station')
            ->assertSeeHtml('class="w-14 px-1 whitespace-nowrap">Badge</th>')
            ->call('addStationToOperator', 1, $this->operator1->id, $this->station1->id)
            // Pastikan kolom Start Shift dan End Shift ada di tabel dengan number input jam & menit
            ->assertSee('Start Shift')
            ->assertSee('End Shift')
            ->assertSeeHtml('type="number"')
            ->assertSeeHtml('max="23"')
            ->assertSeeHtml('max="59"');

        // Lakukan penyesuaian waktu kerja pada operator 1
        $component->set('shiftTimes.1.' . $this->operator1->id . '.start', '08:00')
            ->set('shiftTimes.1.' . $this->operator1->id . '.end', '17:00')
            ->call('saveDraft')
            ->assertSee('Jadwal roster berhasil disimpan sebagai DRAFT.');

        // Verifikasi tersimpan di database roster_entries dalam format waktu valid
        $this->assertDatabaseHas('roster_entries', [
            'manpower_id' => $this->operator1->id,
            'shift' => 1,
            'start_shift' => '08:00',
            'end_shift' => '17:00',
        ]);

        // Verifikasi termuat kembali dengan benar di Livewire component
        $reloaded = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday);

        $shiftTimes = $reloaded->get('shiftTimes');
        $this->assertEquals('08:00', $shiftTimes[1][$this->operator1->id]['start']);
        $this->assertEquals('17:00', $shiftTimes[1][$this->operator1->id]['end']);

        // Verifikasi input non-waktu ditolak dan dinormalisasi menjadi kosong
        $this->assertEquals('', $reloaded->instance()->normalizeTime('bukan_waktu'));
        $this->assertEquals('', $reloaded->instance()->normalizeTime('99:99'));
        $this->assertEquals('07:30', $reloaded->instance()->normalizeTime('07.30'));
    }

    public function test_editing_only_end_shift_preserves_default_start_shift_without_empty_dash(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $sunday = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $roster = Roster::create([
            'line_id' => $this->line->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => $sunday,
            'status' => 'draft',
        ]);

        // Simulasikan hanya mengedit end shift (15:00) tanpa mengedit start shift
        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->call('addOperatorToShift', $this->operator1->id)
            ->assertSee('set station')
            ->assertDontSee('Belum ada station')
            ->assertSeeHtml('class="w-14 px-1 whitespace-nowrap">Badge</th>')
            ->call('addStationToOperator', 1, $this->operator1->id, $this->station1->id)
            ->set('shiftTimes.1.' . $this->operator1->id . '.end', '15:00')
            ->call('saveDraft');

        // Verifikasi di database: start_shift otomatis fallback ke default line (07:00), bukan null/kosong
        $this->assertDatabaseHas('roster_entries', [
            'roster_id' => $roster->id,
            'manpower_id' => $this->operator1->id,
            'shift' => 1,
            'start_shift' => '07:00',
            'end_shift' => '15:00',
        ]);

        // Verifikasi di PDF: start shift tidak bernilai "-" atau "--", melainkan "07:00 - 15:00"
        $roster->refresh();
        $renderedPdf = view('pdf.roster-landscape', [
            'rosters' => [$roster],
            'isSingle' => true,
        ])->render();

        $this->assertStringContainsString('07:00 - 15:00', $renderedPdf);
        $this->assertStringNotContainsString('- - 15:00', $renderedPdf);

        // Verifikasi di Rekap List (modal rekap): juga menampilkan "07:00 - 15:00"
        $component->set('showCrossLineModal', true)
            ->assertSeeHtml('07:00 - 15:00')
            ->assertDontSeeHtml('- - 15:00');
    }

    public function test_user_can_navigate_to_target_line_from_cross_line_modal_to_edit_directly(): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $sunday = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        // Buat Line kedua (Line B)
        $lineB = Line::create([
            'nama' => 'Line 2 Beta',
            'mp_standard' => 12,
            'pola_kerja' => '5_days_2_shift',
            'status' => 'aktif',
        ]);

        // Buat roster untuk Line A dan Line B
        $rosterA = Roster::create([
            'line_id' => $this->line->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => $sunday,
            'status' => 'draft',
        ]);

        $rosterB = Roster::create([
            'line_id' => $lineB->id,
            'leader_id' => $this->leader->id,
            'minggu_mulai' => $monday,
            'minggu_selesai' => $sunday,
            'status' => 'final',
        ]);

        // Buka komponen di Line A
        $component = Livewire::test(RosterSchedule::class)
            ->set('selectedLineId', $this->line->id)
            ->set('weekStart', $monday)
            ->set('showCrossLineModal', true)
            ->assertSee('Line 2 Beta');

        // Navigasi langsung ke Line B dari modal rekap
        $component->call('navigateToLine', $lineB->id);

        // Verifikasi state telah berpindah ke Line B dan modal tertutup
        $this->assertEquals($lineB->id, $component->get('selectedLineId'));
        $this->assertFalse($component->get('showCrossLineModal'));
        $component->assertSee('Line 2 Beta')
            ->assertSee("Beralih ke Line 'Line 2 Beta'");
    }
}
