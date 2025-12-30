<?php

namespace App\Services\Product;

use App\Models\Product;

class ProductService
{
    public function list(array $params = [])
    {
        return Product::with('category')
            ->where('is_active', true)
            ->latest()
            ->paginate($params['limit'] ?? 12);
    }

    public function find(int $id): Product
    {
        return Product::with('category')->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function delete(int $id): bool
    {
        return Product::findOrFail($id)->delete();
    }
}
