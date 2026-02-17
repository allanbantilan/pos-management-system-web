<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosItemController extends Controller
{
    /**
     * Display the POS dashboard.
     */
    public function dashboard(): Response
    {
        // In a real application, you would fetch these from the database
        $categories = [
            'All',
            'Electronics',
            'Clothing',
            'Food & Beverage',
            'Home & Garden',
            'Books',
            'Toys',
            'Sports',
            'Beauty',
            'Automotive'
        ];

        // Sample products - in production, fetch from database
        $products = [
            [
                'id' => 1,
                'name' => 'Wireless Headphones',
                'price' => 89.99,
                'category' => 'Electronics',
                'stock' => 45,
                'image' => 'https://via.placeholder.com/150/6366f1/ffffff?text=Headphones',
                'sku' => 'ELEC-001',
            ],
            [
                'id' => 2,
                'name' => 'Coffee Beans 1kg',
                'price' => 24.99,
                'category' => 'Food & Beverage',
                'stock' => 120,
                'image' => 'https://via.placeholder.com/150/ec4899/ffffff?text=Coffee',
                'sku' => 'FOOD-001',
            ],
            [
                'id' => 3,
                'name' => 'Cotton T-Shirt',
                'price' => 19.99,
                'category' => 'Clothing',
                'stock' => 67,
                'image' => 'https://via.placeholder.com/150/8b5cf6/ffffff?text=T-Shirt',
                'sku' => 'CLTH-001',
            ],
            [
                'id' => 4,
                'name' => 'Smart Watch',
                'price' => 199.99,
                'category' => 'Electronics',
                'stock' => 23,
                'image' => 'https://via.placeholder.com/150/6366f1/ffffff?text=Watch',
                'sku' => 'ELEC-002',
            ],
            [
                'id' => 5,
                'name' => 'Garden Tools Set',
                'price' => 45.50,
                'category' => 'Home & Garden',
                'stock' => 34,
                'image' => 'https://via.placeholder.com/150/10b981/ffffff?text=Tools',
                'sku' => 'HOME-001',
            ],
            [
                'id' => 6,
                'name' => 'Mystery Novel',
                'price' => 14.99,
                'category' => 'Books',
                'stock' => 89,
                'image' => 'https://via.placeholder.com/150/f59e0b/ffffff?text=Book',
                'sku' => 'BOOK-001',
            ],
            [
                'id' => 7,
                'name' => 'Yoga Mat',
                'price' => 29.99,
                'category' => 'Sports',
                'stock' => 56,
                'image' => 'https://via.placeholder.com/150/06b6d4/ffffff?text=Yoga+Mat',
                'sku' => 'SPRT-001',
            ],
            [
                'id' => 8,
                'name' => 'Bluetooth Speaker',
                'price' => 69.99,
                'category' => 'Electronics',
                'stock' => 38,
                'image' => 'https://via.placeholder.com/150/6366f1/ffffff?text=Speaker',
                'sku' => 'ELEC-003',
            ],
            [
                'id' => 9,
                'name' => 'Face Cream',
                'price' => 34.99,
                'category' => 'Beauty',
                'stock' => 72,
                'image' => 'https://via.placeholder.com/150/ec4899/ffffff?text=Cream',
                'sku' => 'BEAU-001',
            ],
            [
                'id' => 10,
                'name' => 'Car Phone Mount',
                'price' => 15.99,
                'category' => 'Automotive',
                'stock' => 91,
                'image' => 'https://via.placeholder.com/150/64748b/ffffff?text=Mount',
                'sku' => 'AUTO-001',
            ],
        ];

        return Inertia::render('POS/Dashboard', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Process a checkout/transaction.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        // In a real application, you would:
        // 1. Create a transaction record
        // 2. Update product stock
        // 3. Process payment
        // 4. Generate receipt
        
        // For now, we'll just return a success response
        return response()->json([
            'success' => true,
            'message' => 'Transaction processed successfully',
            'transaction_id' => 'TXN-' . time(),
            'total' => $request->total,
        ]);
    }

    /**
     * Get products with optional filtering.
     */
    public function getProducts(Request $request)
    {
        $category = $request->input('category');
        $search = $request->input('search');

        // In production, this would be a database query
        // For now, returning static data
        
        return response()->json([
            'products' => [], // Would be filtered products from database
        ]);
    }

    /**
     * Get product by ID.
     */
    public function getProduct($id)
    {
        // In production, fetch from database
        return response()->json([
            'product' => null,
        ]);
    }
}