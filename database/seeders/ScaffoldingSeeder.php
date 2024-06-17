<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Helpers\Helper;
use App\Models\Product;
use App\Models\User;
class ScaffoldingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (User::where('email', 'seller@gmail.com')->doesntExist()) {
            $user = new User();
            $user->name = 'The Seller';
            $user->email = 'seller@gmail.com';
            $user->password = Hash::make('7RJ6vq@Q5+N!n-En');
            $user->address_line_1 = "39 St James's St, St. James's";
            $user->phone = '7818 816728';
            $user->country_dial_code = '44';
            $user->country_iso_code = 'gb';
            $user->country_id = '167';
            $user->city_id = 'London';
            $user->postal_code = 'E1 7DB';
            $user->added_by = 1;
            $user->save();

            $user->roles()->attach([2]);
        }

        if (User::where('email', 'qmw@heel.com')->whereHas('role', fn ($builder) => $builder->where('roles.id', 4))->doesntExist()) {
            $user = new User();
            $user->name = 'Shenzhen Qingmai Bicycle Co., Ltd.';
            $user->email = 'qmw@heel.com';
            $user->password = Hash::make('XfoU$1lF}Kj#3+NA');
            $user->address_line_1 = '';
            $user->phone = '19939287274';
            $user->country_dial_code = '44';
            $user->country_iso_code = 'gb';
            $user->country_id = '167';
            $user->city_id = 'London';
            $user->postal_code = 'E1 7DB';
            $user->added_by = 1;
            $user->save();

            $user->roles()->attach([4]);
        }

        if (User::where('email', 'driver@ebike.com')->doesntExist()) {
            $user = new User();
            $user->name = 'Driver A';
            $user->email = 'driver@ebike.com';
            $user->password = Hash::make('Dpd7Eeq{]Q&c^uLE');
            $user->address_line_1 = 'Scale Space White City, 58 Wood Ln, London W12 7RZ, United Kingdom';
            $user->phone = '7818 446728';
            $user->country_dial_code = '44';
            $user->country_iso_code = 'gb';
            $user->country_id = '167';
            $user->city_id = 'London';
            $user->postal_code = 'W12 7RZ';
            $user->lat = '51.5138049';
            $user->long = '-0.2227208';
            $user->added_by = 1;
            $user->save();

            $user->roles()->attach([3]);
        }

        if (User::where('email', 'driver@gmail.com')->doesntExist()) {
            $user = new User();
            $user->name = 'Driver B';
            $user->email = 'driver@gmail.com';
            $user->password = Hash::make('EYQA()~-IZ}7cK}Y');
            $user->address_line_1 = '94 Old Broad St, London EC2M 1JB, UK';
            $user->phone = '7818 886728';
            $user->country_dial_code = '44';
            $user->country_iso_code = 'gb';
            $user->country_id = '167';
            $user->city_id = 'London';
            $user->postal_code = 'EC2M 3TL';
            $user->lat = '51.5172332';
            $user->long = '-0.0831443';
            $user->added_by = 1;
            $user->save();

            $user->roles()->attach([3]);
        }



        foreach (['Scooter', 'Bike', 'Disable car'] as $category) {
            if (Category::where('slug', Helper::slug($category))->doesntExist()) {
                $user = new Category();
                $user->name = $category;
                $user->slug = Helper::slug($category);
                $user->added_by = 1;
                $user->save();   
            }
        }

        foreach ([
            ['name' => 'D8 Pro Mi Pro H7', 'category' => Category::where('slug', Helper::slug('Scooter'))->first()->id],
            ['name' => 'H7', 'category' => Category::where('slug', Helper::slug('Disable car'))->first()->id]
        ] as $p) {
            $product = new Product();
            $product->unique_number = Helper::generateProductNumber();
            $product->name = $p['name'];
            $product->category_id = $p['category'];
            $product->sales_price = 0;
            $product->added_by = 1;
            $product->save();
        }

    }
}
