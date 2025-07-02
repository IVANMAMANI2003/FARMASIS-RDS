<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'=>'Angel Rosendo Condori Coaquira',
            'email'=>'angel.condori@gmail.com',
            'password'=>bcrypt('12345678')
        ]);
        $this->call(CategorySeeder::class);
        Supplier::factory()->count(100)->create();
        Product::factory(500)->create();
    }
}
