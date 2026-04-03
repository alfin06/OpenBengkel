<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Mechanic;
use App\Models\Part;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeWorkOrders = WorkOrder::with(['vehicle.customer'])
            ->where('status', '!=', 'Selesai')
            ->orderBy('created_at', 'desc')
            ->get();

        $receivablesDueToday = Invoice::with(['workOrder.vehicle.customer'])
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<=', Carbon::today())
            ->get();

        // Note the new folder path: 'Dashboard/Staff'
        return inertia('Dashboard/Staff', [
            'activeWorkOrders' => $activeWorkOrders,
            'receivablesDueToday' => $receivablesDueToday,
        ]);
    }
}