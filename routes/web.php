<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\WorkOrderItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\MechanicController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\Auth\GoogleController;
use \App\Http\Controllers\ExpenseController;
use \App\Http\Controllers\Admin\SettingController;
use \App\Http\Controllers\Admin\UserController;
use \App\Http\Controllers\Admin\ReportController;



Route::get('/invoices/{invoice}/view', [InvoiceController::class, 'showPublic'])->name('invoices.show');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});


Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::resource('/admin/users', UserController::class)->names('admin.users');
    Route::put('/parts/{part}', [PartController::class, 'update'])->name('parts.update');
    Route::delete('/parts/{part}', [PartController::class, 'destroy'])->name('parts.destroy');
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
});

Route::middleware(['auth', 'verified', 'staff'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::post('/customers/{customer}/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    // Page to prepare the bill
    Route::get('/work-orders/{workOrder}/invoice', [InvoiceController::class, 'create'])->name('invoices.create');
    
    Route::get('/work-orders/quick-checkin', function () {
        return inertia('WorkOrders/UnifiedCreate');
    })->name('work-orders.quick-checkin');

    Route::post('/work-orders/unified', [WorkOrderController::class, 'storeUnified'])->name('work-orders.store-unified');
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
    Route::delete('/work-order-parts/{id}', [WorkOrderController::class, 'removePart'])->name('work-orders.remove-part');
    Route::patch('/work-order-parts/{workOrderPart}/qty', [WorkOrderController::class, 'updatePartQty'])->name('work-orders.parts.update-qty');

    // Update/Delete Work Order Items
    Route::patch('/work-order-items/{item}/update', [WorkOrderItemController::class, 'updateTask'])->name('work-order-items.update-task');
    Route::delete('/work-order-items/{item}', [WorkOrderItemController::class, 'destroy'])->name('work-order-items.destroy');
    // Toggle task completion (Check/Uncheck)
    Route::patch('/work-order-items/{item}', [WorkOrderItemController::class, 'update'])->name('work-order-items.update');

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

    // Buku Pengeluaran (Daily Expenses)
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::resource('/mechanics', MechanicController::class)->names('mechanics');


    // Route for the Auto-fill search
    Route::get('/api/vehicles/search', function (Request $request) {
        // Clean the plate number (remove spaces) to match database format
        $plate = strtoupper($request->query('plate'));
        
        // Find the vehicle and include the owner's data
        $vehicle = \App\Models\Vehicle::with('customer')
            ->where('plate_number', $plate)
            ->first();

        return response()->json($vehicle);
    })->name('api.vehicles.search');
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
