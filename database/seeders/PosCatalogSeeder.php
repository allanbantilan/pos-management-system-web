<?php

namespace Database\Seeders;

use App\Enums\ItemUnit;
use App\Models\PosCategory;
use App\Models\PosItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PosCatalogSeeder extends Seeder
{
    /**
     * Seed test POS categories and items.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Burgers', 'description' => 'Signature burgers and sandwiches', 'sort_order' => 1],
            ['name' => 'Chicken', 'description' => 'Crispy and grilled chicken meals', 'sort_order' => 2],
            ['name' => 'Sides', 'description' => 'Fries, rice, and add-ons', 'sort_order' => 3],
            ['name' => 'Drinks', 'description' => 'Cold drinks and hot beverages', 'sort_order' => 4],
            ['name' => 'Desserts', 'description' => 'Sweet treats and ice cream', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            PosCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $items = [
            [
                'name' => 'Classic Cheeseburger',
                'sku' => 'ITEM-BURGER-001',
                'category' => 'Burgers',
                'price' => 129.00,
                'cost' => 68.00,
                'stock' => 120,
                'min_stock' => 20,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000001',
                'image' => 'https://placehold.co/900x700/png?text=Classic+Cheeseburger',
                'description' => 'Beef patty, cheese, lettuce, and house sauce.',
            ],
            [
                'name' => 'Double BBQ Burger',
                'sku' => 'ITEM-BURGER-002',
                'category' => 'Burgers',
                'price' => 169.00,
                'cost' => 92.00,
                'stock' => 80,
                'min_stock' => 15,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000002',
                'image' => 'https://placehold.co/900x700/png?text=Double+BBQ+Burger',
                'description' => 'Double beef patties with smoky BBQ glaze.',
            ],
            [
                'name' => '2pc Crispy Chicken Meal',
                'sku' => 'ITEM-CHICKEN-001',
                'category' => 'Chicken',
                'price' => 189.00,
                'cost' => 101.00,
                'stock' => 70,
                'min_stock' => 12,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000003',
                'image' => 'https://placehold.co/900x700/png?text=2pc+Crispy+Chicken',
                'description' => 'Two-piece fried chicken with gravy.',
            ],
            [
                'name' => 'Spicy Chicken Sandwich',
                'sku' => 'ITEM-CHICKEN-002',
                'category' => 'Chicken',
                'price' => 149.00,
                'cost' => 79.00,
                'stock' => 65,
                'min_stock' => 10,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000004',
                'image' => 'https://placehold.co/900x700/png?text=Spicy+Chicken+Sandwich',
                'description' => 'Crispy spicy chicken fillet with mayo.',
            ],
            [
                'name' => 'Regular Fries',
                'sku' => 'ITEM-SIDES-001',
                'category' => 'Sides',
                'price' => 59.00,
                'cost' => 24.00,
                'stock' => 200,
                'min_stock' => 30,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000005',
                'image' => 'https://placehold.co/900x700/png?text=Regular+Fries',
                'description' => 'Golden crispy fries.',
            ],
            [
                'name' => 'Onion Rings',
                'sku' => 'ITEM-SIDES-002',
                'category' => 'Sides',
                'price' => 69.00,
                'cost' => 29.00,
                'stock' => 150,
                'min_stock' => 20,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000006',
                'image' => 'https://placehold.co/900x700/png?text=Onion+Rings',
                'description' => 'Crispy battered onion rings.',
            ],
            [
                'name' => 'Iced Tea (16oz)',
                'sku' => 'ITEM-DRINK-001',
                'category' => 'Drinks',
                'price' => 49.00,
                'cost' => 16.00,
                'stock' => 180,
                'min_stock' => 25,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000007',
                'image' => 'https://placehold.co/900x700/png?text=Iced+Tea',
                'description' => 'Fresh brewed sweet iced tea.',
            ],
            [
                'name' => 'Cola (22oz)',
                'sku' => 'ITEM-DRINK-002',
                'category' => 'Drinks',
                'price' => 55.00,
                'cost' => 20.00,
                'stock' => 160,
                'min_stock' => 25,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000008',
                'image' => 'https://placehold.co/900x700/png?text=Cola+22oz',
                'description' => 'Cold sparkling cola drink.',
            ],
            [
                'name' => 'Sundae Cup',
                'sku' => 'ITEM-DESSERT-001',
                'category' => 'Desserts',
                'price' => 45.00,
                'cost' => 18.00,
                'stock' => 100,
                'min_stock' => 15,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000009',
                'image' => 'https://placehold.co/900x700/png?text=Sundae+Cup',
                'description' => 'Vanilla sundae with chocolate topping.',
            ],
            [
                'name' => 'Apple Pie',
                'sku' => 'ITEM-DESSERT-002',
                'category' => 'Desserts',
                'price' => 39.00,
                'cost' => 15.00,
                'stock' => 110,
                'min_stock' => 15,
                'unit' => ItemUnit::Piece->value,
                'barcode' => '100000000010',
                'image' => 'https://placehold.co/900x700/png?text=Apple+Pie',
                'description' => 'Warm pastry filled with apple cinnamon.',
            ],
        ];

        foreach ($items as $item) {
            PosItem::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'cost' => $item['cost'],
                    'category' => $item['category'],
                    'stock' => $item['stock'],
                    'min_stock' => $item['min_stock'],
                    'image' => $item['image'],
                    'barcode' => $item['barcode'],
                    'is_active' => true,
                    'unit' => $item['unit'],
                    'metadata' => ['seeded' => true],
                ]
            );
        }
    }
}
