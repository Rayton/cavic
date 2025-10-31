<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //Import Dummy Data
        DB::unprepared(file_get_contents('public/uploads/dummy_data.sql'));

        DB::beginTransaction();

        // Create Super Admin
        DB::table('users')->insert([
            'name'              => 'Super Admin',
            'email'             => 'admin@demo.com',
            'email_verified_at' => now(),
            'password'          => Hash::make('123456'),
            'status'            => 1,
            'profile_picture'   => 'default.png',
            'user_type'         => 'superadmin',
        ]);

        //Create Tenant
        $teannt = DB::table('tenants')->insert([
            'slug'              => 'demo-user',
            'name'              => 'Demo User',
            'membership_type'   => 'member',
            'package_id'        => 7,
            'subscription_date' => now(),
            'valid_to'          => date('Y-m-d', strtotime(now() . ' + 25 years')),
            'status'            => 1,
        ]);

        DB::table('users')->insert([
            'name'            => 'Demo User',
            'email'           => 'user@demo.com',
            'user_type'       => 'admin',
            'tenant_id'       => $teannt,
            'tenant_owner'    => 1,
            'status'          => 1,
            'profile_picture' => 'default.png',
            'password'        => Hash::make('123456'),
        ]);

        DB::table('currency')->insert([
            [
                'full_name'     => 'United States Dollar',
                'name'          => 'USD',
                'exchange_rate' => 1.000000,
                'base_currency' => 1,
                'status'        => 1,
                'tenant_id'     => $teannt,
            ],
            [
                'full_name'     => 'Euro',
                'name'          => 'EUR',
                'exchange_rate' => 0.960000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $teannt,
            ],
            [
                'full_name'     => 'Indian Rupee',
                'name'          => 'INR',
                'exchange_rate' => 87.000000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $teannt,
            ],
            [
                'full_name'     => 'Nigerian Naira',
                'name'          => 'NGN',
                'exchange_rate' => 1502.000000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $teannt,
            ],
            [
                'full_name'     => 'Ghanaian Cedi',
                'name'          => 'GHS',
                'exchange_rate' => 15.500000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $teannt,
            ],
        ]);

        DB::commit();

    }
}
