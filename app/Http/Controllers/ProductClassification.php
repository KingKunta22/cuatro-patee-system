<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;

class ProductClassification extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->get();
        $categories = Category::orderBy('id', 'desc')->get();
        $subcategories = Subcategory::orderBy('id', 'desc')->get();

        return view('product-classification', compact('brands', 'categories', 'subcategories'));
    }

    public function store(Request $request)
    {
        if ($request->input('action') === 'addBrand') {
            $validated = $request->validate([
                'productBrand' => 'required|string|max:255',
            ]);
            Brand::create(['productBrand' => $validated['productBrand']]);
            return back()->with('success', 'Brand added successfully!');
        }

        if ($request->input('action') === 'addCategory') {
            $validated = $request->validate([
                'productCategory' => 'required|string|max:255',
            ]);
            Category::create(['productCategory' => $validated['productCategory']]);
            return back()->with('success', 'Category added successfully!');
        }

        if ($request->input('action') === 'addSubcategory') {
            $validated = $request->validate([
                'productSubcategory' => 'required|string|max:255',
            ]);
            Subcategory::create(['productSubcategory' => $validated['productSubcategory']]);
            return back()->with('success', 'Subcategory added successfully!');
        }

        return back()->with('error', 'Invalid action.');
    }

    
    public function destroyBrand($id)
    {
        Brand::findOrFail($id)->delete();
        return back()->with('success', 'Brand deleted successfully!');
    }

    public function destroyCategory($id)
    {
        Category::findOrFail($id)->delete();
        return back()->with('success', 'Category deleted successfully!');
    }

    public function destroySubcategory($id)
    {
        Subcategory::findOrFail($id)->delete();
        return back()->with('success', 'Subcategory deleted successfully!');
    }
}
