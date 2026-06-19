<?php

use App\Http\Controllers\BrilinkReportPdfController;
use App\Http\Controllers\FinancialReportPdfController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\ServiceReportPdfController;
use App\Http\Controllers\UpahKerjaReportPdfController;
use App\Support\AppSettings;
use Illuminate\Support\Facades\Route;

Route::get('/manifest.webmanifest', PwaManifestController::class);

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/exports/financial-report.pdf', FinancialReportPdfController::class)
        ->name('exports.financial-report.pdf');
    Route::get('/admin/exports/brilink-report.pdf', BrilinkReportPdfController::class)
        ->name('exports.brilink-report.pdf');
    Route::get('/admin/exports/service-report.pdf', ServiceReportPdfController::class)
        ->name('exports.service-report.pdf');
    Route::get('/admin/exports/upah-kerja-report.pdf', UpahKerjaReportPdfController::class)
        ->name('exports.upah-kerja-report.pdf');
});

Route::get('/', function () {
    return view('landing', [
        'appName' => AppSettings::appName(),
        'companyName' => AppSettings::companyName(),
        'address' => AppSettings::address(),
        'phone' => AppSettings::phone(),
        'email' => AppSettings::email(),
        'logoUrl' => AppSettings::logoUrl(),
    ]);
});
