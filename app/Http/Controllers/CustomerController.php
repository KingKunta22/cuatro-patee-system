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
            'customerContactNumber' => 'required',
            'customerEmailAddress' => 'required',
        ]);

        $customerFields['customerStatus'] = 'Active';

        Customer::create($customerFields);

        return redirect()->route('customers.index');
    }

    public function index() {
        $customers = Customer::all();

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
