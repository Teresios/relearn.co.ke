<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // 1. Category distribution
        $categoryCounts = Product::where('is_active', 1)
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        // 2. Price slider min/max
        $minPrice = Product::where('is_active', 1)->min('price') ?? 0;
        $maxPrice = Product::where('is_active', 1)->max('price') ?? 10000;

        // 3. Filtering
        $query = Product::where('is_active', 1);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float)$request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float)$request->input('price_max'));
        }

        // 4. Pagination
        $perPage = 20; // Show 20 products per page
        $products = $query->paginate($perPage)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categoryCounts' => $categoryCounts,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }
}