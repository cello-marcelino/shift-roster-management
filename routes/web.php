<?php

use App\Http\Controllers\RosterExportController;
use App\Livewire\MasterLeader;
use App\Livewire\MasterLine;
use App\Livewire\MasterManpower;
use App\Livewire\RosterSchedule;
use Illuminate\Support\Facades\Route;

// Roster Scheduling (Fitur Utama)
Route::get('/', RosterSchedule::class)->name('roster.index');

// Data Master
Route::get('/manpower', MasterManpower::class)->name('master.manpower');
Route::get('/lines', MasterLine::class)->name('master.lines');
Route::get('/leaders', MasterLeader::class)->name('master.leaders');

// PDF Exports & Inline Views (A4 Landscape)
Route::get('/roster/{id}/view-pdf', [RosterExportController::class, 'viewSingle'])->name('roster.view.single');
Route::get('/roster/{id}/export-pdf', [RosterExportController::class, 'exportSingle'])->name('roster.export.single');
Route::get('/roster/view-merged-pdf', [RosterExportController::class, 'viewMerged'])->name('roster.view.merged');
Route::get('/roster/export-merged-pdf', [RosterExportController::class, 'exportMerged'])->name('roster.export.merged');
