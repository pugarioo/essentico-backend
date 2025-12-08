<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Discount;
use Carbon\Carbon;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'discount_code' => 'SBAPN',
                'value' => 10.00,
                'expiration_date' => Carbon::now()->addYear(),
                'is_active' => true,
            ],
            [
                'discount_code' => 'SYBAU',
                'value' => 15.00,
                'expiration_date' => Carbon::now()->addYear(),
                'is_active' => true,
            ],
            [
                'discount_code' => '676767',
                'value' => 20.00,
                'expiration_date' => Carbon::now()->addYear(),
                'is_active' => true,
            ],
            [
                'discount_code' => 'SYFM',
                'value' => 25.00,
                'expiration_date' => Carbon::now()->addYear(),
                'is_active' => true,
            ],
            [
                'discount_code' => 'TRIPLEB',
                'value' => 30.00,
                'expiration_date' => Carbon::now()->addYear(),
                'is_active' => true,
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(
                ['discount_code' => $discount['discount_code']],
                $discount
            );
        }
    }
}

