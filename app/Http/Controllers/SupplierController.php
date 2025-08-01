<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function store(Request $request) {
        $supplierFields = $request->validate([
            'supplierName'=>'required',
            'supplierAddress'=>'required',
            'supplierContactNumber'=>'required',
            'supplierEmailAddress'=>'required'
        ]);

        // Saves the inputted fields inside the Supplier model
        Supplier::create($supplierFields);

        // Will run index method which also closes the modal after saving
        return redirect()->route('suppliers.index');
    }
    
    // This method will run after every store()
    public function index(){
        // Gets all the suppliers in the database
        $suppliers = Supplier::all();

        // Routes back() to the same current route
        // This also means 'suppliers' => $supplers
        return view('suppliers', compact('suppliers'));
    }

    public function update(Request $request, Supplier $supplier){
        $updatedFields = $request->validate([
            'supplierName' => 'required',
            'supplierAddress' => 'required',
            'supplierContactNumber' => 'required',
            'supplierEmailAddress' => 'required|email',
            'supplierStatus' => 'required|in:active,inactive'
        ]);

        $supplier->update($updatedFields);

        return redirect()->route('suppliers.index');
    }

}
