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


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers/{customer}/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');

    // Page to prepare the bill
    Route::get('/work-orders/{workOrder}/invoice', [InvoiceController::class, 'create'])->name('invoices.create');
    // Action to save the bill
    Route::post('/work-orders/{workOrder}/invoice', [InvoiceController::class, 'store'])->name('invoices.store');
    
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

    // Update/Delete Work Order Items
    Route::patch('/work-order-items/{item}/update', [WorkOrderItemController::class, 'updateTask'])->name('work-order-items.update-task');
    Route::delete('/work-order-items/{item}', [WorkOrderItemController::class, 'destroy'])->name('work-order-items.destroy');
    // Toggle task completion (Check/Uncheck)
    Route::patch('/work-order-items/{item}', [WorkOrderItemController::class, 'update'])->name('work-order-items.update');
 
 });



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
