<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\GlAccountController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoicePaymentController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller((AuthController::class))->group(function () {
    Route::post('/login', 'store');
    Route::post('/logout', 'logout');
});


Route::prefix('invoices')->group(function () {
    Route::post('{invoice}/download', action: [InvoiceController::class, 'generatePdf']);
    Route::get('payments', [InvoicePaymentController::class, 'index']);
    Route::get('{clientId}/pending',[InvoicePaymentController::class, 'fetchPendingClientInvoices']);
});

Route::middleware('is.admin')->group(function () {
    Route::apiResource('clients',ClientController::class)->only('destroy');
    Route::apiResource('services',ServiceController::class)->only('destroy');
    Route::apiResource('projects',ProjectController::class)->only('destroy');
    Route::apiResource('accounts',GlAccountController::class)->only('destroy');
    Route::apiResource('invoices',InvoiceController::class)->only('destroy');
    Route::apiResource('expenses',ExpenseController::class)->only('destroy');
    Route::apiResource('journal-entries',JournalController::class)->only('destroy');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/accounts/min',[GlAccountController::class, 'getAccounts']);
    Route::get('/expenses/expense-no',[ExpenseController::class, 'fetchExpenseNo']);

    Route::get('/forms',[FormController::class, 'index']);
    Route::apiResource('users',UserController::class);
    Route::apiResource('roles',RoleController::class);
    Route::apiResource('clients',ClientController::class)->except('destroy');
    Route::apiResource('services',ServiceController::class)->except(['destroy']);
    Route::apiResource('accounts',GlAccountController::class)->except(['destroy']);
    Route::apiResource('projects',ProjectController::class)->except(['destroy']);
    Route::apiResource('invoices',InvoiceController::class)->except(['destroy']);
    Route::apiResource('expenses',ExpenseController::class)->except(['destroy']);
    Route::controller(InvoicePaymentController::class)->group(function () {
        Route::post('/invoices/{invoice}/payments', 'store');
        Route::get('/invoices/payments', 'index');
        Route::post('/invoices/payments', 'storeBulk');
    });

    Route::get('/journal-entries/journal-no',[JournalController::class,'fetchJournalNo']);
    Route::apiResource('journal-entries',JournalController::class)->except('destroy');
});