<?php

namespace App\Http\Controllers;

use App\Models\PONote;
use Illuminate\Http\Request;

class PONotesController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'note' => 'required|string|max:1000'
        ]);

        PONote::create($validated);

        return redirect()->back()->with('success', 'Note added successfully');
    }

    public function update(Request $request, PONote $po_note)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        $po_note->update($validated);

        return redirect()->back()->with('success', 'Note updated successfully');
    }

    public function destroy(PONote $po_note)
    {
        $po_note->delete();

        return redirect()->back()->with('success', 'Note deleted successfully');
    }
}