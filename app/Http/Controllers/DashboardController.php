<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. ADMIN ROUTE
        if ($user->role === 'admin') {
            $todayRevenue = Invoice::where('status', 'paid')
                ->whereDate('created_at', Carbon::today())
                ->sum('amount_paid');

            $totalReceivables = Invoice::where('status', 'unpaid')->sum('grand_total') 
                            - Invoice::where('status', 'unpaid')->sum('amount_paid');

            // Note the new folder path: 'Dashboard/Admin'
            return inertia('Dashboard/Admin', [
                'todayRevenue' => $todayRevenue,
                'totalReceivables' => $totalReceivables,
                // You can add admin-specific active queues here too
            ]);
        }

        // 2. STAFF ROUTE
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