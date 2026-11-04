<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Roster Manpower</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 10mm 7mm 10mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #111827;
            line-height: 1.25;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        .page-container {
            page-break-inside: avoid;
            page-break-after: always;
        }

        .page-container:last-child {
            page-break-after: avoid;
        }

        /* Header Document */
        .doc-header {
            border-bottom: 2px solid #1f2937;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-title {
            font-size: 13pt;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-meta {
            font-size: 8pt;
            color: #374151;
            text-align: right;
            line-height: 1.35;
        }

        .header-meta strong {
            color: #111827;
        }

        /* Line Info Bar */
        .meta-strip {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 4px 8px;
            margin-bottom: 6px;
            font-size: 8pt;
        }

        .meta-strip-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-strip-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Main Roster Columns */
        .shifts-container {
            width: 100%;
            border-collapse: collapse;
        }

        .shift-column {
            vertical-align: top;
            padding: 0 3px;
        }

        /* Shift Table */
        .shift-card {
            border: 1px solid #d1d5db;
            margin-bottom: 6px;
        }

        .shift-header {
            background-color: #1f2937;
            color: #ffffff;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 8.5pt;
            text-align: center;
            letter-spacing: 0.3px;
        }

        .roster-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .roster-table th {
            background-color: #e5e7eb;
            color: #1f2937;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #d1d5db;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
        }

        .roster-table td {
            border: 1px solid #e5e7eb;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
        }

        .roster-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .badge-mono {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        .operator-name {
            font-weight: 600;
        }

        .station-name {
            color: #374151;
        }

        /* Footer */
        .doc-footer {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
            font-size: 6.5pt;
            color: #4b5563;
            width: 100%;
        }

        .doc-footer table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    @foreach ($rosters as $roster)
        @php
            $line = $roster->line;
            $shiftCount = $line->shift_count;
            $effectiveHours = $line->effective_shift_hours;
            $colWidth = floor(100 / $shiftCount);

            // Group entries per shift and group by operator
            $shiftsData = [];
            $maxRows = 1;
            for ($s = 1; $s <= $shiftCount; $s++) {
                $shiftsData[$s] = $roster->entries
                    ->where('shift', $s)
                    ->groupBy('manpower_id')
                    ->map(function ($entries) {
                        $first = $entries->first();
                        $stations = $entries->pluck('station.nama')->filter()->unique()->join(', ');
                        return [
                            'badge_id' => $first->manpower->badge_id ?? '-',
                            'nama' => $first->manpower->nama ?? '-',
                            'stations' => $stations ?: '-',
                            'start_shift' => $first->start_shift,
                            'end_shift' => $first->end_shift,
                        ];
                    })
                    ->sortBy('nama');

                $maxRows = max($maxRows, $shiftsData[$s]->count());
            }

            // Batas minimal ukuran text (pt) agar tetap nyaman dibaca saat dicetak
            $minFontSize = 6.0;

            // Skala dinamis ukuran font dan padding berdasarkan kepadatan baris ($maxRows) dan kolom ($shiftCount)
            if ($maxRows <= 8) {
                $baseFont = 8.0;
                $rowPad = '3px';
                $lineHeight = '1.2';
            } elseif ($maxRows <= 14) {
                $baseFont = 7.5;
                $rowPad = '2.5px';
                $lineHeight = '1.15';
            } elseif ($maxRows <= 20) {
                $baseFont = 7.0;
                $rowPad = '2px';
                $lineHeight = '1.1';
            } elseif ($maxRows <= 26) {
                $baseFont = 6.5;
                $rowPad = '1.5px';
                $lineHeight = '1.05';
            } else {
                $dyn = 6.5 - (($maxRows - 26) * 0.08);
                $baseFont = max($minFontSize, round($dyn, 1));
                $rowPad = '1px';
                $lineHeight = '1.0';
            }

            if ($shiftCount >= 3 && $baseFont > $minFontSize) {
                $baseFont = max($minFontSize, round($baseFont - 0.4, 1));
            }

            $badgeFontSize = max($minFontSize, $baseFont);
            $nameFontSize = max($minFontSize, $baseFont);
            $subFontSize = max($minFontSize, round($baseFont - 0.4, 1));
            $timeFontSize = max($minFontSize, round($baseFont - 0.2, 1));
            $headerPad = $maxRows > 18 ? '2px' : '3px';
        @endphp

        <div class="page-container">
            <!-- Header Section -->
            <div class="doc-header">
                <table class="header-table">
                    <tr>
                        <td style="width: 60%;">
                            <div class="header-title">ROSTER JADWAL MANPOWER LINE {{ $line->nama }}</div>
                        </td>
                        <td class="header-meta">
                            <div>Start Shift: <strong>{{ $roster->minggu_mulai->format('d/m/Y') }}</strong></div>
                            <div>Leader: <strong>{{ $roster->leader->nama ?? '-' }}</strong></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Meta Strip -->
            <div class="meta-strip">
                <table class="meta-strip-table">
                    <tr>
                        <td style="width: 25%;">Line: <strong>{{ $line->nama }}</strong></td>
                        <td style="width: 25%;">Pola Kerja: <strong>{{ $line->pola_kerja_label }}</strong></td>
                        <td style="width: 25%;">MP Standard: <strong>{{ $line->mp_standard }} MP/Line</strong></td>
                        <td style="width: 25%; text-align: right;">Status: <strong>{{ strtoupper($roster->status) }}</strong></td>
                    </tr>
                </table>
            </div>

            <!-- Shifts Side-by-Side Grid -->
            <table class="shifts-container">
                <tr>
                    @for ($s = 1; $s <= $shiftCount; $s++)
                        @php
                            $shiftEntries = $shiftsData[$s] ?? collect();
                            $shiftHoursInfo = $effectiveHours[$s]['hours'] ?? '-';
                            $shiftLabel = $effectiveHours[$s]['label'] ?? "Shift {$s}";

                            $defaultShiftStart = '07:00';
                            $defaultShiftEnd = '16:00';
                            if (!empty($shiftHoursInfo) && str_contains($shiftHoursInfo, '-')) {
                                $hParts = explode('-', $shiftHoursInfo);
                                $defaultShiftStart = trim(str_replace('.', ':', $hParts[0] ?? '07:00'));
                                $defaultShiftEnd = trim(str_replace('.', ':', $hParts[1] ?? '16:00'));
                            }
                        @endphp
                        <td class="shift-column" style="width: {{ $colWidth }}%;">
                            <div class="shift-card">
                                <div class="shift-header">
                                    <strong>{{ strtoupper($shiftLabel) }}</strong>
                                </div>
                                <table class="roster-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 14%; padding: {{ $headerPad }} 3px; font-size: {{ $subFontSize }}pt;">Badge</th>
                                            <th style="width: 35%; padding: {{ $headerPad }} 4px; font-size: {{ $subFontSize }}pt;">Nama Operator</th>
                                            <th style="width: 25%; padding: {{ $headerPad }} 4px; font-size: {{ $subFontSize }}pt;">Station</th>
                                            <th style="width: 26%; padding: {{ $headerPad }} 3px; font-size: {{ $subFontSize }}pt; text-align: center;">Start - End</th>
                                        </tr>
                                    </thead>
                                    <tbody style="line-height: {{ $lineHeight }};">
                                        @forelse ($shiftEntries as $opRow)
                                            @php
                                                $hasCustom = !empty($opRow['start_shift']) || !empty($opRow['end_shift']);
                                                if ($hasCustom) {
                                                    $effStart = !empty($opRow['start_shift']) ? $opRow['start_shift'] : $defaultShiftStart;
                                                    $effEnd = !empty($opRow['end_shift']) ? $opRow['end_shift'] : $defaultShiftEnd;
                                                    $timeDisplay = "{$effStart} - {$effEnd}";
                                                } else {
                                                    $timeDisplay = $shiftHoursInfo;
                                                }
                                            @endphp
                                            <tr>
                                                <td class="badge-mono" style="padding: {{ $rowPad }} 3px; font-size: {{ $badgeFontSize }}pt;">{{ $opRow['badge_id'] }}</td>
                                                <td class="operator-name" style="padding: {{ $rowPad }} 4px; font-size: {{ $nameFontSize }}pt;">{{ $opRow['nama'] }}</td>
                                                <td class="station-name" style="padding: {{ $rowPad }} 4px; font-size: {{ $subFontSize }}pt;">{{ $opRow['stations'] }}</td>
                                                <td style="text-align: center; font-family: 'Courier New', Courier, monospace; font-size: {{ $timeFontSize }}pt; font-weight: bold; color: #000000; padding: {{ $rowPad }} 3px;">
                                                    {{ $timeDisplay }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" style="text-align: center; color: #9ca3af; padding: 8px; font-size: {{ $subFontSize }}pt;">
                                                    Belum ada penugasan pada shift ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background-color: #f3f4f6; font-weight: bold;">
                                            <td colspan="3" style="font-size: {{ $subFontSize }}pt; padding: {{ $rowPad }} 4px; text-align: right;">Total Operator:</td>
                                            <td style="font-size: {{ $subFontSize }}pt; padding: {{ $rowPad }} 3px; text-align: center; font-weight: bold; color: #000000;">{{ $shiftEntries->count() }} orang</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </td>
                    @endfor
                </tr>
            </table>

            <!-- Footer Document -->
            <div class="doc-footer">
                <table>
                    <tr>
                        <td style="width: 100%;">
                            <cite>note : pastikan nama dicek dengan benar dan jangan ada yang salah masuk shift, hubungi leader jika ada hal yang ingin ditanyakan, bagi nama yang tidak ada di list masuk ke Shift 1 ({{ $effectiveHours[1]['hours'] ?? '07.00' }})</cite>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endforeach
</body>
</html>
