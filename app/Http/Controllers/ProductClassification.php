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
        $search = request('search');
        
        $brands = Brand::when($search, function($query, $search) {
            $query->where('productBrand', 'like', "%{$search}%");
        })->orderBy('productBrand', 'asc')->get();

        $categories = Category::when($search, function($query, $search) {
            $query->where('productCategory', 'like', "%{$search}%");
        })->orderBy('productCategory', 'desc')->get();

        $subcategories = Subcategory::when($search, function($query, $search) {
            $query->where('productSubcategory', 'like', "%{$search}%");
        })->orderBy('productSubcategory', 'desc')->get();

        return view('product-classification', compact('brands', 'categories', 'subcategories'));
    }

    public function store(Request $request)
    {

        if ($request->input('action') === 'addBrand') {
            $validated = $request->validate([
                'productBrand' => 'required|string|max:255|unique:brands,productBrand',
            ], [
                'productBrand.unique' => 'This brand name already exists.',
            ]);
            
            Brand::create(['productBrand' => $validated['productBrand']]);
            return back()->with('success', 'Brand added successfully!');
        }

        if ($request->input('action') === 'addCategory') {
            $validated = $request->validate([
                'productCategory' => 'required|string|max:255|unique:categories,productCategory',
            ], [
                'productCategory.unique' => 'This category name already exists.',
            ]);
            
            Category::create(['productCategory' => $validated['productCategory']]);
            return back()->with('success', 'Category added successfully!');
        }

        if ($request->input('action') === 'addSubcategory') {
            $validated = $request->validate([
                'productSubcategory' => 'required|string|max:255|unique:subcategories,productSubcategory',
            ], [
                'productSubcategory.unique' => 'This subcategory name already exists.',
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