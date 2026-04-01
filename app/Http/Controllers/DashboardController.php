<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $activeWorkOrders = WorkOrder::with(['vehicle', 'items'])
            ->whereIn('status', ['Pending', 'Proses'])
            ->orderBy('status', 'desc') // Shows 'Proses' at the top, then 'Pending'
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'activeWorkOrders' => $activeWorkOrders
        ]);
    }
}