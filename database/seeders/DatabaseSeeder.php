<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        User::create([
            'name' => 'Admin',
            'email' => 'admin@kasirku.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@kasirku.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'is_active' => true
        ]);

        // Create categories
        $categories = [
            ['name' => 'Makanan', 'slug' => 'makanan'],
            ['name' => 'Minuman', 'slug' => 'minuman'],
            ['name' => 'Snack', 'slug' => 'snack'],
            ['name' => 'Rokok', 'slug' => 'rokok'],
            ['name' => 'ATK', 'slug' => 'atk'],
            ['name' => 'Kebutuhan Rumah', 'slug' => 'kebutuhan-rumah'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create products
        $products = [
            ['category_id' => 1, 'name' => 'Indomie Goreng', 'barcode' => '8991002101202', 'price' => 3500, 'stock' => 100],
            ['category_id' => 1, 'name' => 'Indomie Soto', 'barcode' => '8991002101203', 'price' => 3000, 'stock' => 80],
            ['category_id' => 1, 'name' => 'Mie Sedaap Goreng', 'barcode' => '8994295100011', 'price' => 3500, 'stock' => 60],
            ['category_id' => 2, 'name' => 'Aqua 600ml', 'barcode' => '8992001001012', 'price' => 4000, 'stock' => 200],
            ['category_id' => 2, 'name' => 'Aqua 1500ml', 'barcode' => '8992001001015', 'price' => 8000, 'stock' => 100],
            ['category_id' => 2, 'name' => 'Teh Pucuk 350ml', 'barcode' => '8992761002131', 'price' => 4500, 'stock' => 150],
            ['category_id' => 2, 'name' => 'Teh Botol Sosro 450ml', 'barcode' => '8992761002132', 'price' => 5000, 'stock' => 120],
            ['category_id' => 2, 'name' => 'Coca Cola 390ml', 'barcode' => '8999999020361', 'price' => 7000, 'stock' => 80],
            ['category_id' => 2, 'name' => 'Sprite 390ml', 'barcode' => '8999999020362', 'price' => 7000, 'stock' => 80],
            ['category_id' => 3, 'name' => 'Chitato Original', 'barcode' => '8886013300212', 'price' => 12000, 'stock' => 50],
            ['category_id' => 3, 'name' => 'Lays BBQ', 'barcode' => '8886013300213', 'price' => 12000, 'stock' => 45],
            ['category_id' => 3, 'name' => 'Pocky Coklat', 'barcode' => '8992775005507', 'price' => 15000, 'stock' => 35],
            ['category_id' => 3, 'name' => 'Oreo Original', 'barcode' => '7622210100474', 'price' => 10000, 'stock' => 60],
            ['category_id' => 4, 'name' => 'Sampoerna Mild 16', 'barcode' => '8999999034566', 'price' => 28000, 'stock' => 25],
            ['category_id' => 4, 'name' => 'Gudang Garam Surya 12', 'barcode' => '8999999034567', 'price' => 26000, 'stock' => 30],
            ['category_id' => 5, 'name' => 'Pulpen Faster', 'barcode' => '8993189880014', 'price' => 3500, 'stock' => 60],
            ['category_id' => 5, 'name' => 'Buku Tulis Sidu', 'barcode' => '8993189880015', 'price' => 5000, 'stock' => 40],
            ['category_id' => 6, 'name' => 'Sabun Lifebuoy', 'barcode' => '8999999045678', 'price' => 4500, 'stock' => 50],
            ['category_id' => 6, 'name' => 'Pasta Gigi Pepsodent 75g', 'barcode' => '8999999045679', 'price' => 8000, 'stock' => 40],
            ['category_id' => 6, 'name' => 'Shampoo Clear 170ml', 'barcode' => '8999999045680', 'price' => 25000, 'stock' => 30],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Create default settings
        Setting::set('store_name', 'Toko KasirKu');
        Setting::set('store_address', 'Jl Trans Sulawesi, Buntulia Tengah, Kec. Buntulia, Kabupaten Pohuwato');
        Setting::set('store_phone', '021-1234567');
        Setting::set('tax_rate', '11');
    }
}
