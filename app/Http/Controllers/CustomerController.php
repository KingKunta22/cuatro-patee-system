<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function store(Request $request) {
        $customerFields = $request->validate([
            'customerName' => 'required',
            'customerAddress' => 'required',
            'customerContactNumber' => 'required', 'size:11',
            'customerEmailAddress' => 'required',
        ]);

        $customerFields['customerStatus'] = 'Active';

        Customer::create($customerFields);

        return redirect()->route('customers.index');
    }

    public function index(Request $request) {


        // Start query
        $query = Customer::query();

        // Apply search filter
        if ($request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('customerName', 'LIKE', "%{$searchTerm}%")
                ->orWhere('customerEmailAddress', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply ordering and pagination on the query you built
        $customers = $query->orderBy('id', 'DESC')
                        ->paginate(8)
                        ->withQueryString();

        return view('customers', compact('customers'));
    }

    public function update(Request $request, Customer $customer){
        $updatedFields = $request->validate([
            'customerName' => 'required',
            'customerAddress' => 'required',
            'customerContactNumber' => 'required',
            'customerEmailAddress' => 'required|email',
            'customerStatus' => 'required|in:Active,Inactive',
        ]);

        $customer->update($updatedFields);

        return redirect()->route('customers.index');

    }

    public function destroy(Customer $customer) {
        $customer->delete();

        return redirect()->route('customers.index');
    }

}
