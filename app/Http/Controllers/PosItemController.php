<?php

namespace App\Http\Controllers;

use App\Models\PosCategory;
use App\Models\PosItem;
use App\Models\Receipt;
use App\Models\Transaction;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PosItemController extends Controller
{
    public function __construct(
        private readonly ReceiptService $receiptService,
    ) {
    }

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

        $checkoutReceipt = null;
        $receiptNumber = trim((string) $request->input('receipt', ''));
        if ($receiptNumber !== '') {
            $transaction = Transaction::query()
                ->where('receipt_number', $receiptNumber)
                ->where('user_id', $request->user()->id)
                ->with(['items.item'])
                ->first();

            if ($transaction) {
                $checkoutReceipt = $this->receiptService->buildPayload($transaction);
            }
        }

        $recentReceipts = Receipt::query()
            ->where('user_id', $request->user()->id)
            ->latest('issued_at')
            ->latest('id')
            ->limit(5)
            ->get([
                'id',
                'receipt_number',
                'payment_method',
                'total',
                'issued_at',
            ])
            ->map(fn (Receipt $receipt): array => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'payment_method' => $receipt->payment_method,
                'total' => (float) $receipt->total,
                'issued_at' => optional($receipt->issued_at)->toDateTimeString(),
            ])
            ->values()
            ->all();

        return Inertia::render('PosDashboard', [
            'categories' => $categories,
            'items' => $items,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
            'checkoutResult' => $request->input('checkout_result'),
            'checkoutReceipt' => $checkoutReceipt,
            'recentReceipts' => $recentReceipts,
        ]);
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
