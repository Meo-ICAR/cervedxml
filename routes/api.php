<?php

use App\Http\Controllers\API\ExternalReportController;
use App\Http\Controllers\API\ReportController;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register API routes for your application. These
 * | routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "api" middleware group. Make something great!
 * |
 */

Route::post('/cerved-report', [ExternalReportController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    // Report resource routes
    Route::apiResource('reports', ReportController::class);

    // Additional route for uploading XML files
    Route::post('reports/{report}/upload-xml', [ReportController::class, 'uploadXml'])
        ->name('reports.upload-xml');
});
