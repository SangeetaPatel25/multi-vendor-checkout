<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'user' => [
                    'name' => 'John Electronics',
                    'email' => 'john@techstore.com',
                ],
                'vendor' => [
                    'name' => 'TechStore Pro',
                    'description' => 'Premium electronics and gadgets',
                ],
            ],
            [
                'user' => [
                    'name' => 'Sarah Fashion',
                    'email' => 'sarah@fashionhub.com',
                ],
                'vendor' => [
                    'name' => 'FashionHub',
                    'description' => 'Trendy clothing and accessories',
                ],
            ],
            [
                'user' => [
                    'name' => 'Mike Home',
                    'email' => 'mike@homedecor.com',
                ],
                'vendor' => [
                    'name' => 'HomeDecor Plus',
                    'description' => 'Beautiful home decoration items',
                ],
            ],
        ];

        foreach ($vendors as $entry) {
            $user = User::updateOrCreate(
                ['email' => $entry['user']['email']],
                [
                    'name' => $entry['user']['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'customer',
                ]
            );

            Vendor::updateOrCreate(
                ['user_id' => $user->id],
                $entry['vendor']
            );
        }
    }
}
