<?php

use App\Domains\Purchases\Http\Controllers\Company\ExpenseCategoriesController;
use App\Domains\Purchases\Http\Controllers\Company\ExpensesController;
use Illuminate\Support\Facades\Route;

Route::get('/expenses/{expense}/show/receipt', [ExpensesController::class, 'showReceipt']);
Route::post('/expenses/{expense}/upload/receipts', [ExpensesController::class, 'uploadReceipt']);
Route::match(['POST'], 'expenses/delete', [ExpensesController::class, 'delete']);
Route::resource('expenses', ExpensesController::class)->except(['create', 'edit']);
Route::resource('categories', ExpenseCategoriesController::class)->except(['create', 'edit']);
