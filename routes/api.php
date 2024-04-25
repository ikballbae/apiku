<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResponseController;
use Illuminate\Support\Facades\Route;

Route::post('v1/auth/login', [AuthController::class, 'login']);
Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('v1/auth/logout', [AuthController::class, 'logout']);

    // Route to create a new form
    Route::post('v1/forms', [FormController::class, 'store']);

    // Route to retrieve all forms for the authenticated user
    Route::get('v1/forms', [FormController::class, 'index']);

    Route::get('v1/forms/{slug}', [FormController::class, 'show']);

    Route::post('v1/forms/{formSlug}/questions', [QuestionController::class, 'store']);

    Route::delete('v1/forms/{formSlug}/questions/{questionId}', [QuestionController::class, 'destroy']);

    Route::post('/v1/forms/{formSlug}/responses', [ResponseController::class, 'store']);
    Route::get('/v1/forms/{formSlug}/responses', [ResponseController::class, 'index']);
});
