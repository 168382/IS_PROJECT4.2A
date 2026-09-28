<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Seeds the standard set of lost & found categories so the app has
     * sensible defaults out of the box, mirroring what used to live in
     * config/categories.php before categories were moved into the database.
     */
    public function up(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Student ID Card', 'description' => 'University student or staff identity card'],
            ['name' => 'Mobile Phone', 'description' => 'Smartphones and feature phones'],
            ['name' => 'Laptop', 'description' => 'Laptops, notebooks, and tablets'],
            ['name' => 'Wallet', 'description' => 'Wallets, purses, and card holders'],
            ['name' => 'Bag / Backpack', 'description' => 'Backpacks, handbags, and carry bags'],
            ['name' => 'Keys', 'description' => 'House, room, car, or cabinet keys'],
            ['name' => 'Books / Notes', 'description' => 'Textbooks, notebooks, and study materials'],
            ['name' => 'Charger / Cable', 'description' => 'Phone/laptop chargers and power cables'],
            ['name' => 'Earphones / Headphones', 'description' => 'Wireless earbuds, AirPods, or headphones'],
            ['name' => 'Water Bottle', 'description' => 'Re-usable water bottles and flasks'],
            ['name' => 'Other', 'description' => 'Miscellaneous items'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insertOrIgnore($category + [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')->whereIn('name', [
            'Student ID Card', 'Mobile Phone', 'Laptop', 'Wallet', 'Bag / Backpack',
            'Keys', 'Books / Notes', 'Charger / Cable', 'Earphones / Headphones',
            'Water Bottle', 'Other',
        ])->delete();
    }
};
