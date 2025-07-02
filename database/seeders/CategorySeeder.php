<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder{
    public function run(): void{
        Category::create([
            'name'=>'Analgésicos'
        ]);
        Category::create([
            'name'=>'Antiácidos y antiulcerosos'
        ]);
        Category::create([
            'name'=>'Antialérgicos'
        ]);
        Category::create([
            'name'=>'Antidiarreicos'
        ]);
        Category::create([
            'name'=>'Laxantes'
        ]);
        Category::create([
            'name'=>'Antiinflamatorios'
        ]);
        Category::create([
            'name'=>'Antimicrobianos'
        ]);
        Category::create([
            'name'=>'Antipiréticos'
        ]);
        Category::create([
            'name'=>'Mucolíticos'
        ]);
        Category::create([
            'name'=>'Antitusivos'
        ]);
    }
}
