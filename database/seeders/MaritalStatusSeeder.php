<?php

namespace Database\Seeders;

use App\Models\MaritalStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaritalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maritalDetails = ['Married', 'Single', 'Devorced'];

        foreach ($maritalDetails as $maritalDetail) {
            MaritalStatus::create([
                'marital_status' => $maritalDetail,
            ]);
        }
    }
}
