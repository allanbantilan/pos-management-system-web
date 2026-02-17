<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PosItemController extends Controller
{
    /**
     * Display the POS dashboard.
     */
    public function dashboard(): Response
    {
        $categories = PosItem::query()
            ->where('is_active', true)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->prepend('All')
            ->values();

        $products = PosItem::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'category', 'stock', 'image', 'sku']);

        return Inertia::render('PosDashboard', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Process a checkout/transaction.
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:pos_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $result = DB::transaction(function () use ($request) {
            $requestedItems = collect($request->input('items'))
                ->groupBy('id')
                ->map(function ($groupedItems, $id) {
                    return [
                        'id' => (int) $id,
                        'quantity' => (int) $groupedItems->sum('quantity'),
                    ];
                })
                ->values();

            $itemIds = $requestedItems->pluck('id')->unique()->values();

            $posItems = PosItem::query()
                ->where('is_active', true)
                ->whereIn('id', $itemIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($posItems->count() !== $itemIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more products are unavailable.',
                ]);
            }

            $subtotal = 0.0;
            $tax = 0.0;
            $transactionItems = [];
            $stockDeductions = [];

            foreach ($requestedItems as $requestedItem) {
                $item = $posItems->get((int) $requestedItem['id']);
                $quantity = (int) $requestedItem['quantity'];

                if ($item->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$item->name}.",
                    ]);
                }

                $linePrice = (float) $item->price;
                $lineSubtotal = $linePrice * $quantity;
                $lineTaxRate = $item->is_taxable ? (float) $item->tax_rate : 0.0;
                $lineTax = round($lineSubtotal * ($lineTaxRate / 100), 2);

                $subtotal += $lineSubtotal;
                $tax += $lineTax;

                $transactionItems[] = [
                    'pos_item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $linePrice,
                    'subtotal' => $lineSubtotal,
                    'discount' => 0,
                    'tax' => $lineTax,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $stockDeductions[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                ];
            }

            $discount = 0.0;
            $total = round(($subtotal - $discount) + $tax, 2);
            $receiptNumber = 'RCPT-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

            $transactionId = DB::table('transactions')->insertGetId([
                'user_id' => $request->user()->id,
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->input('payment_method', 'cash'),
                'status' => 'completed',
                'receipt_number' => $receiptNumber,
                'notes' => $request->input('notes'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($transactionItems as &$transactionItem) {
                $transactionItem['transaction_id'] = $transactionId;
            }
            unset($transactionItem);

            DB::table('transaction_items')->insert($transactionItems);

            foreach ($stockDeductions as $deduction) {
                $deduction['item']->decrement('stock', $deduction['quantity']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction processed successfully.',
                'transaction_id' => $transactionId,
                'receipt_number' => $receiptNumber,
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'total' => $total,
            ]);
        });

        return $result;
    }

    /**
     * Get products with optional filtering.
     */
    public function getProducts(Request $request): JsonResponse
    {
        $category = $request->input('category');
        $search = $request->input('search');

        $query = PosItem::query()->where('is_active', true);

        if (!empty($category) && $category !== 'All') {
            $query->where('category', $category);
        }

        if (!empty($search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $products = $query
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'category', 'stock', 'image', 'sku', 'barcode']);

        return response()->json([
            'products' => $products,
        ]);
    }

    /**
     * Get product by ID.
     */
    public function getProduct(int $id): JsonResponse
    {
        $product = PosItem::query()
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json([
            'product' => $product,
        ]);
    }
}
