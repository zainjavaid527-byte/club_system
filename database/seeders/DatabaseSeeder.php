<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerSession;
use App\Models\Game;
use App\Models\Product;
use App\Models\SessionItem;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 0. Users (login credentials)
        |--------------------------------------------------------------------------
        */

        $this->call([
            UserSeeder::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 1. Customers - 10 Entries
        |--------------------------------------------------------------------------
        */

        $customers = [
            ['name' => 'Ali Raza', 'phone' => '03001234567'],
            ['name' => 'Ahmed Khan', 'phone' => '03011234567'],
            ['name' => 'Usman Ali', 'phone' => '03021234567'],
            ['name' => 'Hamza Sheikh', 'phone' => '03031234567'],
            ['name' => 'Bilal Ahmad', 'phone' => '03041234567'],
            ['name' => 'Hassan Raza', 'phone' => '03051234567'],
            ['name' => 'Zain Abbas', 'phone' => '03061234567'],
            ['name' => 'Talha Khan', 'phone' => '03071234567'],
            ['name' => 'Saad Ahmed', 'phone' => '03081234567'],
            ['name' => 'Usman Khan', 'phone' => '03091234567'],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Products - 10 Entries
        |--------------------------------------------------------------------------
        */

        $products = [
            ['name' => 'Coke', 'price' => 100],
            ['name' => 'Pepsi', 'price' => 100],
            ['name' => 'Chips', 'price' => 80],
            ['name' => 'Tea', 'price' => 100],
            ['name' => 'Coffee', 'price' => 150],
            ['name' => 'Water Bottle', 'price' => 60],
            ['name' => 'Juice', 'price' => 120],
            ['name' => 'Sandwich', 'price' => 250],
            ['name' => 'Burger', 'price' => 350],
            ['name' => 'Biscuits', 'price' => 70],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Customer Sessions - 10 Entries
        |--------------------------------------------------------------------------
        */

        $sessions = [];

        foreach ($customers as $index => $customerData) {
            $customer = Customer::where('phone', $customerData['phone'])->first();

            $session = CustomerSession::create([
                'customer_id' => $customer->id,
                'started_at' => now()->subHours(10 - $index),
                'closed_at' => null,
                'total_amount' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'status' => 'active',
            ]);

            $sessions[] = $session;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Games - 10 Entries
        |--------------------------------------------------------------------------
        */

        $gameRates = [
            100,
            100,
            150,
            100,
            150,
            100,
            100,
            150,
            100,
            150,
        ];

        foreach ($sessions as $index => $session) {
            Game::create([
                'customer_session_id' => $session->id,
                'game_type' => 'Snooker',
                'rate' => $gameRates[$index],
                'played_at' => now()->subHours(9 - $index),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Canteen / Session Items - 10 Entries
        |--------------------------------------------------------------------------
        */

        $quantities = [
            2,
            1,
            3,
            2,
            1,
            2,
            1,
            2,
            1,
            3,
        ];

        foreach ($sessions as $index => $session) {
            $product = Product::find($index + 1);

            $quantity = $quantities[$index];

            $total = $product->price * $quantity;

            SessionItem::create([
                'customer_session_id' => $session->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
                'total' => $total,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Payments - 10 Entries
        |--------------------------------------------------------------------------
        */

        foreach ($sessions as $index => $session) {

            $gameTotal = $session->games()->sum('rate');

            $itemTotal = $session->items()->sum('total');

            $total = $gameTotal + $itemTotal;

            /*
            | Some customers fully paid
            | Some customers have remaining/udhaar
            */

            if ($index % 3 === 0) {
                // Full payment
                $paymentAmount = $total;
            } elseif ($index % 3 === 1) {
                // Partial payment
                $paymentAmount = floor($total / 2);
            } else {
                // Small payment
                $paymentAmount = min(100, $total);
            }

            Payment::create([
                'customer_session_id' => $session->id,
                'amount' => $paymentAmount,
                'payment_method' => 'cash',
                'paid_at' => now()->subHours(1),
            ]);

            $remaining = $total - $paymentAmount;

            /*
            | Update session bill
            */

            $session->update([
                'total_amount' => $total,
                'paid_amount' => $paymentAmount,
                'remaining_amount' => $remaining,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Done
        |--------------------------------------------------------------------------
        */

        $this->command->info('1 Admin user created successfully.');
        $this->command->info('10 Customers created successfully.');
        $this->command->info('10 Products created successfully.');
        $this->command->info('10 Customer Sessions created successfully.');
        $this->command->info('10 Games created successfully.');
        $this->command->info('10 Canteen Items created successfully.');
        $this->command->info('10 Payments created successfully.');
    }
}