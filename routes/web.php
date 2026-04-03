<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PartController;


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers/{customer}/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    // Page to prepare the bill
    Route::get('/work-orders/{workOrder}/invoice', [WorkOrderController::class, 'createInvoice'])->name('invoices.create');
    
    // The main Check-In page (License Plate Search)
    Route::get('/work-orders/check-in', [WorkOrderController::class, 'create'])->name('work-orders.create');
    // The action to save the Work Order
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    // Add a task to a specific work order
    Route::post('/work-orders/{workOrder}/items', [WorkOrderItemController::class, 'store'])->name('work-orders.items.store');
    // Update Work Order Header
    Route::patch('/work-orders/{workOrder}', [WorkOrderController::class, 'update'])->name('work-orders.update');
    Route::patch('/work-orders/{workOrder}/start', [WorkOrderController::class, 'startWork'])->name('work-orders.start');
    // Add this inside your authenticated routes group
    Route::post('/work-orders/{workOrder}/parts', [WorkOrderController::class, 'addPart'])->name('work-orders.parts.store');
    // Also, while you're there, add the delete route for parts
    Route::delete('/work-order-parts/{workOrderPart}', [WorkOrderController::class, 'removePart'])->name('work-orders.parts.destroy');

    // Update/Delete Work Order Items
    Route::patch('/work-order-items/{item}/update', [WorkOrderItemController::class, 'updateTask'])->name('work-order-items.update-task');
    Route::delete('/work-order-items/{item}', [WorkOrderItemController::class, 'destroy'])->name('work-order-items.destroy');
    // Toggle task completion (Check/Uncheck)
    Route::patch('/work-order-items/{item}', [WorkOrderItemController::class, 'update'])->name('work-order-items.update');
 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/parts', [PartController::class, 'index'])->name('parts.index');
    Route::post('/parts', [PartController::class, 'store'])->name('parts.store');
    Route::post('/parts/{part}/restock', [PartController::class, 'restock'])->name('parts.restock');
    // Show the scanning page
    Route::get('/parts/restock', [PartController::class, 'restockPage'])->name('parts.restock.page');
    Route::get('/parts/search', [PartController::class, 'search'])->name('parts.search');

    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
   // View all unpaid invoices
    Route::get('/receivables', [App\Http\Controllers\InvoiceController::class, 'receivables'])->name('receivables.index');
    // Mark an invoice as paid
    Route::put('/invoices/{invoice}/settle', [App\Http\Controllers\InvoiceController::class, 'settle'])->name('invoices.settle');
 });



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
