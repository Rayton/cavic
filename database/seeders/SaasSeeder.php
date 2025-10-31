<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaasSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($tenantId): void {
        DB::table('currency')->insert([
            [
                'full_name'     => 'United States Dollar',
                'name'          => 'USD',
                'exchange_rate' => 1.000000,
                'base_currency' => 1,
                'status'        => 1,
                'tenant_id'     => $tenantId,
            ],
            [
                'full_name'     => 'Euro',
                'name'          => 'EUR',
                'exchange_rate' => 0.960000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $tenantId,
            ],
            [
                'full_name'     => 'Indian Rupee',
                'name'          => 'INR',
                'exchange_rate' => 87.000000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $tenantId,
            ],
            [
                'full_name'     => 'Nigerian Naira',
                'name'          => 'NGN',
                'exchange_rate' => 1502.000000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $tenantId,
            ],
            [
                'full_name'     => 'Ghanaian Cedi',
                'name'          => 'GHS',
                'exchange_rate' => 15.500000,
                'base_currency' => 0,
                'status'        => 1,
                'tenant_id'     => $tenantId,
            ],
        ]);
    }
}
