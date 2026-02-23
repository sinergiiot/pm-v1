<?php

use App\Http\Controllers\ProjectCashFlowExportController;
use App\Http\Controllers\ProjectDocumentVersionDownloadController;
use App\Http\Controllers\ProjectProfitLossExportController;
use App\Http\Controllers\ProjectShareController;
use App\Http\Controllers\TaskImportTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/share/{token}', [ProjectShareController::class, 'show'])->name('share.project');

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('files/document-version/{version}/download', ProjectDocumentVersionDownloadController::class)
        ->name('filament.admin.resources.projects.files.download-version');
    Route::get('projects/{project}/profit-loss/export', ProjectProfitLossExportController::class)
        ->name('filament.admin.resources.projects.profit-loss.export');
    Route::get('projects/{project}/cash-flow/export', ProjectCashFlowExportController::class)
        ->name('filament.admin.resources.projects.cash-flow.export');
    Route::get('projects/{project}/task-import-template', TaskImportTemplateController::class)
        ->name('filament.admin.resources.projects.task-import-template');
});
