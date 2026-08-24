<?php

use App\Platform\Mail\Http\Admin\MailConfigurationController;
use App\Platform\Mail\Http\Company\CompanyMailConfigurationController;
use Illuminate\Support\Facades\Route;

Route::controller(MailConfigurationController::class)->group(function () {
    Route::get('/mail/drivers', 'getMailDrivers');
    Route::get('/mail/config', 'getMailEnvironment');
    Route::post('/mail/config', 'saveMailEnvironment');
    Route::post('/mail/test', 'testEmailConfig');
});

Route::get('/company/mail/config', [CompanyMailConfigurationController::class, 'getDefaultConfig']);
Route::get('/company/mail/company-config', [CompanyMailConfigurationController::class, 'getMailConfig']);
Route::post('/company/mail/company-config', [CompanyMailConfigurationController::class, 'saveMailConfig']);
Route::post('/company/mail/company-test', [CompanyMailConfigurationController::class, 'testMailConfig']);
