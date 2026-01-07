<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    
    public function run()
    {
        DB::table('setting')->insert([
            'setting_id'       => 1,
            'branch_id'        => 1,
            'company_name'     => 'AlHaseeb Traders',
            'address'          => 'Bharia Town Phase 4, Rawalpindi, Pakistan',
            'phone'            => '03001234567',
            'receipt_type'     => 1,
            'discount'         => 5,
            'logo_path'        => '/img/logo.png',
            'member_card_path' => '/img/member.png',
        ]);
    }
}
