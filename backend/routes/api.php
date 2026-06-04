<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/users', [UserController::class, 'index']);

    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::put('/documents/{document}', [DocumentController::class, 'update']);
    Route::patch('/documents/{document}/rename', [DocumentController::class, 'rename']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    Route::post('/documents/{document}/share', [ShareController::class, 'store']);
    Route::delete('/documents/{document}/share/{user}', [ShareController::class, 'destroy']);

    Route::post('/documents/import', [UploadController::class, 'importAsNew']);
    Route::post('/documents/{document}/import', [UploadController::class, 'importIntoExisting']);
    Route::post('/documents/{document}/attachments', [UploadController::class, 'attach']);
    Route::get('/documents/{document}/attachments/{attachment}/download', [UploadController::class, 'download']);
});
