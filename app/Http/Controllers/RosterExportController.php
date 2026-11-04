<?php

namespace App\Http\Controllers;

use App\Models\Line;
use App\Models\Roster;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RosterExportController extends Controller
{
    /**
     * Lihat satu line PDF (stream inline di browser tanpa unduh)
     */
    public function viewSingle(int $id)
    {
        return $this->generateSinglePdf($id, 'stream');
    }

    /**
     * Unduh satu line PDF dalam format A4 Landscape
     */
    public function exportSingle(Request $request, int $id)
    {
        $mode = $request->query('mode') === 'view' ? 'stream' : 'download';
        return $this->generateSinglePdf($id, $mode);
    }

    /**
     * Lihat gabungan beberapa line dalam PDF (stream inline di browser tanpa unduh)
     */
    public function viewMerged(Request $request)
    {
        return $this->generateMergedPdf($request, 'stream');
    }

    /**
     * Unduh gabungan beberapa line dalam satu file PDF A4 Landscape
     */
    public function exportMerged(Request $request)
    {
        $mode = $request->query('mode') === 'view' ? 'stream' : 'download';
        return $this->generateMergedPdf($request, $mode);
    }

    /**
     * Generator dokumen PDF untuk Single Line
     */
    protected function generateSinglePdf(int $id, string $mode)
    {
        $roster = Roster::with(['line.stations', 'leader', 'entries.manpower', 'entries.station'])
            ->findOrFail($id);

        $line = $roster->line;

        $pdf = Pdf::loadView('pdf.roster-landscape', [
            'rosters' => [$roster],
            'isSingle' => true,
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ]);

        $fileName = sprintf(
            'roster_%s_%s.pdf',
            str_replace(' ', '_', strtolower($line->nama)),
            $roster->minggu_mulai->format('Y-m-d')
        );

        return $mode === 'stream'
            ? $pdf->stream($fileName)
            : $pdf->download($fileName);
    }

    /**
     * Generator dokumen PDF untuk Merged Lines
     */
    protected function generateMergedPdf(Request $request, string $mode)
    {
        $weekStart = $request->query('week_start');
        $lineIds = $request->query('line_ids', []);

        if (!$weekStart) {
            $weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }

        $startDate = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $query = Roster::with(['line.stations', 'leader', 'entries.manpower', 'entries.station'])
            ->whereDate('minggu_mulai', $startDate);

        if (!empty($lineIds) && is_array($lineIds)) {
            $query->whereIn('line_id', $lineIds);
        }

        $rosters = $query->get();

        if ($rosters->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data roster untuk ditampilkan pada minggu tersebut.');
        }

        $pdf = Pdf::loadView('pdf.roster-landscape', [
            'rosters' => $rosters,
            'isSingle' => false,
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ]);

        $fileName = sprintf('roster_gabungan_%s.pdf', $startDate);

        return $mode === 'stream'
            ? $pdf->stream($fileName)
            : $pdf->download($fileName);
    }
}
