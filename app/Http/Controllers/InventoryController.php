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
            'productBrand' => 'required|in:Pedigree,Whiskas,Royal Canin,Cesar,Acana',
            'productCategory' => 'required|in:dogFoodDry,dogFoodWet,catFoodDry,catFoodWet,dogToy',
            'productSellingPrice' => 'required|numeric|min:0|gte:productCostPrice',
            'productCostPrice' => 'required|numeric|min:0',
            'productImage' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validate image
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
            $validated['image'] = $imagePath; // Save path to DB
        }

        // Save to database
        Inventory::create([
            'productName' => $validated['productName'],
            'productBrand' => $validated['productBrand'],
            'productCategory' => $validated['productCategory'],
            'sellingPrice' => $validated['productSellingPrice'],
            'costPrice' => $validated['productCostPrice'],
            'profitMargin' => $profitMargin,
            'image' => $validated['image'] ?? null,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Product added!');
    }

    private function generateSKU()
    {

    }
}
