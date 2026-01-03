<?php
namespace App\Services\Product;

use Exception;
use App\Models\Category;

class CategoryService{
    
    public function getMenuTree(){

        $recursiveLoad = function($query) use (&$recursiveLoad){
            $query  ->where('is_active', true)
                    ->with(['children' => $recursiveLoad]);
        };

        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => $recursiveLoad])
            ->orderBy('id', 'asc')
            ->get();
    }
}