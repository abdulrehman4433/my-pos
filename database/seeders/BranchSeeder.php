<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Head Office',
            'code' => 'BR-001',
            'phone' => '03000000000',
            'address' => 'Bharia Tow Branch, Rawalpindi, Pakistan',
            'is_active' => true,
        ]);
    }
}
