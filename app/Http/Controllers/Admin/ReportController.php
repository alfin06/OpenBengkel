<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the financial reports dashboard.
     */
    public function index(Request $request)
    {
        // 1. Set Filters (Default to current month and year)
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // 2. Monthly Revenue (Sum grand_total from Invoices)
        $revenue = Invoice::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('grand_total');

        // 3. Monthly Expenses (Sum amount from Expenses)
        $expenses = Expense::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');

        // 4. Monthly Profit & Margin Calculation
        $profit = $revenue - $expenses;
        $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

        // 5. Annual Totals (The "Big Picture")
        $annualRevenue = Invoice::whereYear('created_at', $year)->sum('grand_total');
        $annualExpenses = Expense::whereYear('created_at', $year)->sum('amount');

        // 6. Top 5 Best Selling Items/Services
        // Note: Using 'item_name' as discussed. Change to 'name' if your schema differs.
        $topItems = DB::table('invoice_items')
            ->select(
                'item_name as name', 
                DB::raw('SUM(qty) as total_qty'), 
                DB::raw('SUM(subtotal) as revenue')
            )
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('item_name')
            ->orderBy('revenue', 'DESC')
            ->limit(5)
            ->get();

        // 7. Daily Breakdown for Chart (Invoices per Day)
        $dailyRevenue = Invoice::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // 8. Generate List of Available Years for Filter
        $availableYears = range(2024, Carbon::now()->year + 1);

        return Inertia::render('Reports/Index', [
            'stats' => [
                'revenue' => (float)$revenue,
                'expenses' => (float)$expenses,
                'profit' => (float)$profit,
                'margin' => (float)$margin,
                'annualRevenue' => (float)$annualRevenue,
                'annualExpenses' => (float)$annualExpenses,
            ],
            'chartData' => $dailyRevenue,
            'topItems' => $topItems,
            'availableYears' => $availableYears,
            'filters' => [
                'month' => (int)$month,
                'year' => (int)$year
            ]
        ]);
    }
}