<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Models\PosItem;
use App\Models\PosCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PosItemController extends Controller
{
    /**
     * Display the POS dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $category = (string) $request->input('category', 'All');
        $perPage = 6;

        $categories = PosItem::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $managedCategories = PosCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        $categories = $managedCategories
            ->merge($categories)
            ->unique()
            ->sort()
            ->prepend('All')
            ->values();

        $itemsQuery = PosItem::query()
            ->where('is_active', true);

        if ($category !== 'All' && $category !== '') {
            $itemsQuery->where('category', $category);
        }

        if ($search !== '') {
            $itemsQuery->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $items = $itemsQuery
            ->with('media')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (PosItem $item) {
                $item->image = $this->resolveItemImage($item);

                return $item->only([
                    'id',
                    'name',
                    'price',
                    'category',
                    'stock',
                    'image',
                    'sku',
                ]);
            });

        return Inertia::render('PosDashboard', [
            'categories' => $categories,
            'items' => $items,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    /**
     * Process a checkout/transaction.
     */
    public function checkout(PosCheckoutRequest $request): JsonResponse
    {
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
                    'items' => 'One or more items are unavailable.',
                ]);
            }

            $subtotal = 0.0;
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

                $subtotal += $lineSubtotal;

                $transactionItems[] = [
                    'pos_item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $linePrice,
                    'subtotal' => $lineSubtotal,
                    'discount' => 0,
                    'tax' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $stockDeductions[] = [
                    'item' => $item,
                    'quantity' => $quantity,
                ];
            }

            $discount = 0.0;
            $tax = 0.0;
            $total = round($subtotal - $discount, 2);
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
     * Get items with optional filtering.
     */
    public function getItems(Request $request): JsonResponse
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

        $items = $query
            ->with('media')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'category', 'stock', 'image', 'sku', 'barcode'])
            ->map(function (PosItem $item) {
                $item->image = $this->resolveItemImage($item);

                return $item;
            });

        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * Get item by ID.
     */
    public function getItem(int $id): JsonResponse
    {
        $item = PosItem::query()
            ->where('is_active', true)
            ->with('media')
            ->findOrFail($id);

        $item->image = $this->resolveItemImage($item);

        return response()->json([
            'item' => $item,
        ]);
    }

    private function resolveItemImage(PosItem $item): ?string
    {
        if ($item->hasMedia('item-images')) {
            return $item->getFirstMediaUrl('item-images');
        }

        $image = $item->image;

        if (blank($image)) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return Storage::url($image);
    }
}
