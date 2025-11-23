<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InvoiceController;

Route::get('/invoices',[InvoiceController::class, 'index']);
Route::get('/invoices/{invoice}',[InvoiceController::class, 'show']);
