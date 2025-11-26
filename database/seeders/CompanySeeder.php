<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Company::create([
            'name' => 'Siliwangi Trans',
            'address' => 'Lt. D1 blok Q no. 06, Balubur Town Square, Jl. Tamansari No.7, Tamansari, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40132',
            'email' => 'info@siltrans.com',
        ]);
    }
}
