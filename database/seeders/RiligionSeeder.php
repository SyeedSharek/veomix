<?php

namespace Database\Seeders;

use App\Models\Riligion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RiligionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $riligious = ['Islam', 'Hindu', 'Bordus'];

        foreach ($riligious as $riligion) {
            Riligion::create([
                'marital_status' => $riligion,
            ]);
        }
    }
}
