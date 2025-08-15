<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return view('inventory');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'productName' => 'required',
            'productSKU' => 'required',
            'productBrand' => 'required|in:Pedigree,Whiskas,Royal Canin,Cesar,Acana',
            'productCategory' => 'required|in:dogFoodDry,dogFoodWet,catFoodDry,catFoodWet,dogToy',
            'productStock' => 'required|numeric|min:0',
            'productSellingPrice' => 'required|numeric|min:0',
            'productCostPrice' => 'required|numeric|min:0',
            'productExpDate' => 'required|date|after:today',
            'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Calculate profit margin
        $profitMargin = round(
            ($validated['productSellingPrice'] - $validated['productCostPrice']) / 
            $validated['productCostPrice'] * 100, 
            2
        );

        // Handle image upload
        if ($request->hasFile('productImage')) {
            $imagePath = $request->file('productImage')->store('inventory', 'public');
            $validated['image'] = $imagePath;
        }

        // Save to database
        Inventory::create([
            'productName' => $validated['productName'],
            'productSKU' => $validated['productSKU'],
            'productBrand' => $validated['productBrand'],
            'productCategory' => $validated['productCategory'],
            'productStock' => $validated['productStock'],
            'productSellingPrice' => $validated['productSellingPrice'],
            'productCostPrice' => $validated['productCostPrice'],
            'productProfitMargin' => $profitMargin,
            'productExpirationDate' => $validated['productExpDate'],
            'productImage' => $validated['image'] ?? null,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product added!');
    }

    private function generateSKU()
    {

    }
}
