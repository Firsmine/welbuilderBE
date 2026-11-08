<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // pages
    Route::get('pages', [PageController::class, 'index']);
    Route::post('pages', [PageController::class, 'store']);
    Route::get('pages/{pageSlug}', [PageController::class, 'show']);
    Route::put('pages/{pageSlug}', [PageController::class, 'update']);
    Route::delete('pages/{pageSlug}', [PageController::class, 'destroy']);

    // templates
    Route::get('templates', [TemplateController::class, 'index']);
    Route::get('templates/{templateSlug}', [TemplateController::class, 'show']);

    // sections
    Route::post('pages/{pageSlug}/sections', [SectionController::class, 'store']);
    Route::put('pages/{pageSlug}/sections/{sectionId}/fields', [SectionController::class, 'update']);
    Route::put('pages/{pageSlug}/sections/reorder', [SectionController::class, 'reorder']);
    Route::delete('pages/{pageSlug}/sections/{sectionId}', [SectionController::class, 'destroy']);
});
