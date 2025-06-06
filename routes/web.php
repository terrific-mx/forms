<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormSubmissionController;

Route::post('/f/{form:ulid}', [FormSubmissionController::class, 'store']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
