<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Mechanic;
use App\Models\Part;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $filter = $request->query('filter', 'month'); 
        $now = Carbon::now();

        // 1. Financial Metrics
        $totalIncome = Invoice::where('status', 'paid')
                      ->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year)
                      ->sum('amount_paid');
        $totalExpense = Expense::whereMonth('expense_date', $now->month)->whereYear('expense_date', $now->year)->sum('amount');
        
        // 2. Chart Data (Logic remains exactly the same as before...)
        $chartLabels = [];
        $incomeData = [];
        $expenseData = [];

        if ($filter === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $chartLabels[] = $date->translatedFormat('D');
                $incomeData[] = Invoice::whereDate('created_at', $date->format('Y-m-d'))->sum('grand_total');
                $expenseData[] = Expense::whereDate('expense_date', $date->format('Y-m-d'))->sum('amount');
            }
        } elseif ($filter === 'month') {
            for ($i = 1; $i <= $now->daysInMonth; $i++) {
                $date = $now->copy()->startOfMonth()->addDays($i - 1);
                $chartLabels[] = $i;
                $incomeData[] = Invoice::whereDate('created_at', $date->format('Y-m-d'))->sum('grand_total');
                $expenseData[] = Expense::whereDate('expense_date', $date->format('Y-m-d'))->sum('amount');
            }
        } elseif ($filter === 'year') {
            for ($i = 1; $i <= 12; $i++) {
                $date = $now->copy()->startOfYear()->addMonths($i - 1);
                $chartLabels[] = $date->translatedFormat('M');
                $incomeData[] = Invoice::whereMonth('created_at', $i)->whereYear('created_at', $now->year)->sum('grand_total');
                $expenseData[] = Expense::whereMonth('expense_date', $i)->whereYear('expense_date', $now->year)->sum('amount');
            }
        }

        // 3. Mechanic Performance
        $mechanics = Mechanic::withCount(['workOrders' => function ($query) use ($now) {
            $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
        }])->get()->map(function ($mechanic) {
            return [
                'name' => $mechanic->name,
                'total_cars' => $mechanic->work_orders_count,
                // 'total_commission' => $mechanic->work_orders_count * 3000, 
            ];
        })->sortByDesc('total_cars')->values();

        // 4. Operational Metrics
        $activeWorkOrders = WorkOrder::whereIn('status', ['Pending', 'In Progress'])->count();
        $todayCheckins = WorkOrder::whereDate('created_at', Carbon::today())->count();
        $lowStockItems = Part::where('stock', '<=', 5)->count();

        return Inertia::render('Dashboard/Admin', [
            'metrics' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net_profit' => $totalIncome - $totalExpense,
                'month_name' => $now->translatedFormat('F Y'),
            ],
            'chart' => [
                'labels' => $chartLabels,
                'income' => $incomeData,
                'expense' => $expenseData,
                'current_filter' => $filter,
            ],
            'mechanics' => $mechanics,
            'active_work_orders' => $activeWorkOrders,
            'today_checkins' => $todayCheckins,
            'low_stock_items' => $lowStockItems,
        ]);
    }
}