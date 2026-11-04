<?php

namespace App\Services;

use App\Models\Leader;
use App\Models\Line;
use App\Models\Manpower;
use App\Models\Station;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MasterDataService
{
    /**
     * Mengambil daftar manpower dengan filter pencarian dan status
     */
    public function getManpowers(string $search = '', string $status = '', int $perPage = 25): LengthAwarePaginator
    {
        $query = Manpower::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('badge_id', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query->orderBy('nama', 'asc')->paginate($perPage);
    }

    /**
     * Mengambil seluruh operator aktif untuk opsi pilihan roster
     */
    public function getActiveOperatorsForRoster(): Collection
    {
        return Manpower::where('status', 'aktif')
            ->orderBy('nama', 'asc')
            ->get();
    }

    /**
     * Simpan atau update manpower
     */
    public function saveManpower(array $data, ?int $id = null): Manpower
    {
        if ($id) {
            $manpower = Manpower::findOrFail($id);
            $manpower->update($data);
            return $manpower;
        }

        return Manpower::create($data);
    }

    /**
     * Toggle status aktif/cuti manpower
     */
    public function toggleManpowerStatus(int $id): Manpower
    {
        $manpower = Manpower::findOrFail($id);
        $manpower->status = $manpower->status === 'aktif' ? 'cuti' : 'aktif';
        $manpower->save();
        return $manpower;
    }

    /**
     * Hapus manpower
     */
    public function deleteManpower(int $id): bool
    {
        $manpower = Manpower::findOrFail($id);
        return (bool) $manpower->delete();
    }

    /**
     * Mengambil daftar semua line
     */
    public function getLines(): Collection
    {
        return Line::withCount('stations')->orderBy('nama', 'asc')->get();
    }

    /**
     * Mengambil line beserta stasiunnya
     */
    public function getLineWithStations(int $lineId): ?Line
    {
        return Line::with(['stations' => function ($q) {
            $q->orderBy('nama', 'asc');
        }])->find($lineId);
    }

    /**
     * Simpan atau update line
     */
    public function saveLine(array $data, ?int $id = null): Line
    {
        if ($id) {
            $line = Line::findOrFail($id);
            $line->update($data);
            return $line;
        }

        return Line::create($data);
    }

    /**
     * Hapus line beserta stasiun dan roster terkait
     */
    public function deleteLine(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $line = Line::findOrFail($id);
            foreach ($line->rosters as $roster) {
                $roster->entries()->delete();
                $roster->delete();
            }
            $line->stations()->delete();
            return (bool) $line->delete();
        });
    }

    /**
     * Simpan stasiun untuk sebuah line
     */
    public function saveStation(int $lineId, array $data, ?int $stationId = null): Station
    {
        $data['line_id'] = $lineId;

        if ($stationId) {
            $station = Station::where('line_id', $lineId)->findOrFail($stationId);
            $station->update($data);
            return $station;
        }

        return Station::create($data);
    }

    /**
     * Hapus stasiun
     */
    public function deleteStation(int $stationId): bool
    {
        $station = Station::findOrFail($stationId);
        return (bool) $station->delete();
    }

    /**
     * Mengambil daftar leader aktif
     */
    public function getActiveLeaders(): Collection
    {
        return Leader::where('status', 'aktif')->orderBy('nama', 'asc')->get();
    }

    /**
     * Simpan atau update leader
     */
    public function saveLeader(array $data, ?int $id = null): Leader
    {
        if ($id) {
            $leader = Leader::findOrFail($id);
            $leader->update($data);
            return $leader;
        }

        return Leader::create($data);
    }

    /**
     * Hapus leader
     */
    public function deleteLeader(int $id): bool
    {
        $leader = Leader::findOrFail($id);
        return (bool) $leader->delete();
    }
}
