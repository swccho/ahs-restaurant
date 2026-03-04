<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoRestaurantDataSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'demo-restaurant')->orWhere('id', 1)->first();
        if (! $restaurant) {
            $this->command->warn('Demo restaurant not found. Run DemoAdminSeeder first.');
            return;
        }

        $restaurant->update(['slug' => 'demo-restaurant']);

        $categories = $this->seedCategories($restaurant);
        $menuItems = $this->seedMenuItems($restaurant, $categories);
        $this->seedOffers($restaurant);
        $this->seedOrders($restaurant, $menuItems);
    }

    /** @return array<int, Category> */
    private function seedCategories(Restaurant $restaurant): array
    {
        $data = [
            ['name' => 'Starters', 'sort_order' => 1],
            ['name' => 'Main Course', 'sort_order' => 2],
            ['name' => 'Desserts', 'sort_order' => 3],
        ];
        $categories = [];
        foreach ($data as $i => $row) {
            $categories[$i] = Category::firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'name' => $row['name'],
                ],
                [
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ]
            );
        }
        return $categories;
    }

    /**
     * @param array<int, Category> $categories
     * @return array<int, MenuItem>
     */
    private function seedMenuItems(Restaurant $restaurant, array $categories): array
    {
        $items = [
            ['name' => 'Soup of the Day', 'slug' => 'soup-of-the-day', 'category_index' => 0, 'price' => 5.99, 'description' => 'Chef\'s daily soup'],
            ['name' => 'Garlic Bread', 'slug' => 'garlic-bread', 'category_index' => 0, 'price' => 4.50, 'description' => 'Toasted with garlic butter'],
            ['name' => 'Caesar Salad', 'slug' => 'caesar-salad', 'category_index' => 0, 'price' => 7.99, 'description' => 'Romaine, parmesan, croutons'],
            ['name' => 'Grilled Chicken', 'slug' => 'grilled-chicken', 'category_index' => 1, 'price' => 14.99, 'description' => 'Herb-marinated chicken breast'],
            ['name' => 'Beef Burger', 'slug' => 'beef-burger', 'category_index' => 1, 'price' => 12.99, 'discount_price' => 10.99, 'description' => 'Angus beef, lettuce, tomato', 'is_featured' => true],
            ['name' => 'Fish & Chips', 'slug' => 'fish-and-chips', 'category_index' => 1, 'price' => 13.99, 'description' => 'Beer-battered cod with fries'],
            ['name' => 'Vegetable Pasta', 'slug' => 'vegetable-pasta', 'category_index' => 1, 'price' => 11.99, 'description' => 'Seasonal vegetables, olive oil'],
            ['name' => 'Steak Frites', 'slug' => 'steak-frites', 'category_index' => 1, 'price' => 18.99, 'description' => 'Ribeye with french fries', 'is_featured' => true],
            ['name' => 'Chocolate Cake', 'slug' => 'chocolate-cake', 'category_index' => 2, 'price' => 6.99, 'description' => 'Rich chocolate layer cake'],
            ['name' => 'Ice Cream Sundae', 'slug' => 'ice-cream-sundae', 'category_index' => 2, 'price' => 5.50, 'description' => 'Vanilla ice cream, toppings', 'is_available' => false],
        ];

        $menuItems = [];
        foreach ($items as $i => $row) {
            $catIndex = $row['category_index'];
            $categoryId = $categories[$catIndex]->id;
            $menuItems[$i] = MenuItem::firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'slug' => $row['slug'],
                ],
                [
                    'category_id' => $categoryId,
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'price' => $row['price'],
                    'discount_price' => $row['discount_price'] ?? null,
                    'is_available' => $row['is_available'] ?? true,
                    'is_featured' => $row['is_featured'] ?? false,
                    'sort_order' => $i + 1,
                ]
            );
        }
        return $menuItems;
    }

    private function seedOffers(Restaurant $restaurant): void
    {
        $now = now();
        // Active + valid now
        Offer::firstOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'title' => '10% Off First Order',
            ],
            [
                'description' => 'Get 10% off your first order',
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 15,
                'coupon_code' => 'WELCOME10',
                'starts_at' => $now,
                'ends_at' => $now->copy()->addMonths(1),
                'is_active' => true,
            ]
        );
        // Scheduled future
        Offer::firstOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'title' => 'Summer Special',
            ],
            [
                'description' => 'Coming soon: 15% off next month',
                'type' => 'percentage',
                'value' => 15,
                'min_order_amount' => 20,
                'coupon_code' => 'SUMMER15',
                'starts_at' => $now->copy()->addDays(30),
                'ends_at' => $now->copy()->addMonths(2),
                'is_active' => true,
            ]
        );
        // Inactive
        Offer::firstOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'title' => 'Free Delivery',
            ],
            [
                'description' => 'Free delivery on orders over $25',
                'type' => 'free_delivery',
                'value' => null,
                'min_order_amount' => 25,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addWeeks(2),
                'is_active' => false,
            ]
        );
    }

    /**
     * @param array<int, MenuItem> $menuItems
     */
    private function seedOrders(Restaurant $restaurant, array $menuItems): void
    {
        $admin = User::where('email', DemoAdminSeeder::DEMO_EMAIL)->first();

        $ordersData = [
            [
                'status' => 'pending',
                'customer_name' => 'Alice Smith',
                'customer_phone' => '+1234567890',
                'customer_address' => '123 Main St',
                'order_note' => null,
                'delivery_type' => 'delivery',
                'items' => [
                    ['menu_item' => $menuItems[3], 'qty' => 1],
                    ['menu_item' => $menuItems[8], 'qty' => 2],
                ],
            ],
            [
                'status' => 'preparing',
                'customer_name' => 'Bob Jones',
                'customer_phone' => '+1987654321',
                'customer_address' => null,
                'order_note' => 'Extra napkins please',
                'delivery_type' => 'pickup',
                'items' => [
                    ['menu_item' => $menuItems[4], 'qty' => 2],
                    ['menu_item' => $menuItems[0], 'qty' => 1],
                ],
            ],
            [
                'status' => 'delivered',
                'customer_name' => 'Carol White',
                'customer_phone' => '+1555123456',
                'customer_address' => '456 Oak Ave',
                'order_note' => null,
                'delivery_type' => 'delivery',
                'items' => [
                    ['menu_item' => $menuItems[7], 'qty' => 1],
                    ['menu_item' => $menuItems[9], 'qty' => 1],
                ],
            ],
        ];

        foreach ($ordersData as $data) {
            $subtotal = 0;
            $orderItemsData = [];
            foreach ($data['items'] as $row) {
                $item = $row['menu_item'];
                $price = $item->discount_price ?? $item->price;
                $qty = $row['qty'];
                $lineTotal = round($price * $qty, 2);
                $subtotal += $lineTotal;
                $orderItemsData[] = [
                    'menu_item_id' => $item->id,
                    'item_name_snapshot' => $item->name,
                    'unit_price_snapshot' => $price,
                    'qty' => $qty,
                    'line_total' => $lineTotal,
                ];
            }
            $deliveryFee = $data['delivery_type'] === 'delivery' ? 3.00 : 0;
            $total = round($subtotal + $deliveryFee, 2);

            $order = Order::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'customer_phone' => $data['customer_phone'],
                ],
                [
                    'status' => $data['status'],
                    'customer_name' => $data['customer_name'],
                    'customer_address' => $data['customer_address'] ?? null,
                    'order_note' => $data['order_note'],
                    'delivery_type' => $data['delivery_type'],
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => 0,
                    'total' => $total,
                    'status_updated_at' => $data['status'] !== 'pending' ? now() : null,
                    'status_updated_by' => $data['status'] !== 'pending' ? $admin?->id : null,
                ]
            );

            foreach ($orderItemsData as $itemData) {
                OrderItem::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'menu_item_id' => $itemData['menu_item_id'],
                    ],
                    [
                        'item_name_snapshot' => $itemData['item_name_snapshot'],
                        'unit_price_snapshot' => $itemData['unit_price_snapshot'],
                        'qty' => $itemData['qty'],
                        'line_total' => $itemData['line_total'],
                    ]
                );
            }
        }
    }
}
