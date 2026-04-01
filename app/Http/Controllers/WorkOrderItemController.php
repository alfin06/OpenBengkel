<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Http\Request;

class WorkOrderItemController extends Controller
{
    public function store(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
        ]);

        $workOrder->items()->create([
            'task_name' => $validated['task_name'],
            'is_completed' => false,
        ]);

        return redirect()->back()->with('success', 'Tugas ditambahkan!');
    }

    // This will be for the mechanics to check off tasks later
    public function update(Request $request, WorkOrderItem $item)
    {
        $item->update([
            'is_completed' => !$item->is_completed
        ]);

        return redirect()->back();
    }

    public function updateTask(Request $request, WorkOrderItem $item)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Instruksi diperbarui!');
    }

    public function destroy(WorkOrderItem $item)
    {
        $item->delete();
        return redirect()->back()->with('success', 'Instruksi dihapus!');
    }
}