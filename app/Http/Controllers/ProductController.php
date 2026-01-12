<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
    $items = \App\Models\Product::active()
        ->with('images')
        ->paginate(9);

    // Gán alias
    $products = $items;

    return view('pages.products', compact('products'));
    }

    public function show($slug)
    {
    $product = \App\Models\Product::where('slug', $slug)
        ->with('images')
        ->firstOrFail();

    return view('pages.product-detail', compact('product'));
    }

}
