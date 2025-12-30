<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;

        $this->middleware('permission:products,view')->only('index');
        $this->middleware('permission:products,create')->only(['create','store']);
        $this->middleware('permission:products,update')->only(['edit','update']);
        $this->middleware('permission:products,delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $products = $this->productService->paginate($request->all());
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $this->productService->create($request->all());
        return redirect()->route('admin.products.index');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->productService->update($product, $request->all());
        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        return redirect()->back();
    }
}
