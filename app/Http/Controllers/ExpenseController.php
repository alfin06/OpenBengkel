<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        // Fetch expenses for the current month, newest first
        $expenses = Expense::with('user')
            ->whereMonth('expense_date', date('m'))
            ->whereYear('expense_date', date('Y'))
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string',
            'expense_date' => 'required|date',
        ]);

        // Automatically attach the currently logged-in user's ID
        $request->user()->expenses()->create($validated);

        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Catatan pengeluaran dihapus.');
    }
}