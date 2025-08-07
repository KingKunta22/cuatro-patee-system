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
            'supplierContactNumber'=>'required', 'size:11',
            'supplierEmailAddress'=>'required',
        ]);

        // Set default status
        $supplierFields['supplierStatus'] = 'Active';
        
        // Saves the inputted fields inside the Supplier model
        Supplier::create($supplierFields);

        // Runs index method which also closes the modal after saving
        return redirect()->route('suppliers.index');
    }
    
    // This method will run after every method call
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
            'supplierStatus' => 'required|in:Active,Inactive',
        ]);

        $supplier->update($updatedFields);

        return redirect()->route('suppliers.index');
    }

    public function destroy(Supplier $supplier){

        $supplier->delete();

        return redirect()->route('suppliers.index');
    }

}
