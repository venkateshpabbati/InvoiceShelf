<?php

use App\Platform\Operations\Http\AppVersionController;
use Illuminate\Support\Facades\Route;

// Build probe. Unauthenticated on purpose: the SPA shell and the updater both
// read it before any session or company context exists.
Route::get('app/version', AppVersionController::class);
