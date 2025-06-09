<?php

use App\Http\Controllers\FormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::post('/f/{form:ulid}', [FormSubmissionController::class, 'store']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
