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
            'supplierContactNumber'=>'required|size:11',
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
    public function index(Request $request)
    {
        // Start query
        $query = Supplier::query();

        // Apply search filter
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('supplierName', 'LIKE', "%{$searchTerm}%")
                ->orWhere('supplierEmailAddress', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply ordering and pagination on the query you built
        $suppliers = $query->orderBy('id', 'DESC')
                        ->paginate(8)
                        ->withQueryString();

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
