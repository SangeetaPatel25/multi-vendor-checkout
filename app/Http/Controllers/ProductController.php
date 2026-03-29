<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display all products
     */
    public function index()
    {
        $products = Product::with('vendor')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'vendor' => [
                    'id' => $product->vendor->id,
                    'name' => $product->vendor->name,
                ],
            ];
        });

        return $this->apiResponse(['products' => $products]);
    }
}
