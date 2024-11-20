<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Color;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\Shoe;
use App\Models\ShoeImage;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $masterRole = Role::create([
            'name' => 'Master',
            'description' => 'Owner of the shop',
        ]);

        $customerRole = Role::create([
            'name' => 'Costumer',
            'description' => 'Costumer or buyer in the shop',
        ]);

        // Create users
        $masterUser = User::factory()->create([
            'role_id' => $masterRole->id,
            'name' => 'Master User',
            'email' => 'master@example.com',
        ]);

        $customerUser = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create brands
        $brands = Brand::factory(5)->create();

        // Create shoes and related data
        $shoes = $brands->map(function ($brand) {
            return Shoe::factory(3)->create(['brand_id' => $brand->id]);
        })->flatten();

        // Create shoe images
        $shoes->each(function ($shoe) {
            ShoeImage::factory(3)->create(['shoe_id' => $shoe->id]);
        });

        // Create sizes and colors
        $sizes = Size::factory(5)->create();
        $colors = Color::factory(5)->create();

        // Create inventory
        $inventoryItems = $shoes->flatMap(function ($shoe) use ($sizes, $colors) {
            return $sizes->flatMap(function ($size) use ($shoe, $colors) {
                return $colors->map(function ($color) use ($shoe, $size) {
                    return Inventory::factory()->create([
                        'shoe_id' => $shoe->id,
                        'size_id' => $size->id,
                        'color_id' => $color->id,
                    ]);
                });
            });
        });

        // Create orders and order items
        $orders = Order::factory(2)->create(['user_id' => $customerUser->id]);

        $orders->each(function ($order) use ($inventoryItems) {
            OrderItem::factory(3)->create([
                'order_id' => $order->id,
                'inventory_id' => $inventoryItems->random()->id,
            ]);
        });

        // Create cart and cart items
        $cart = Cart::factory()->create(['user_id' => $customerUser->id]);

        CartItem::factory(3)->create([
            'cart_id' => $cart->id,
            'inventory_id' => $inventoryItems->random()->id,
        ]);
    }
}