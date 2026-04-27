<?php

namespace Database\Seeders;

use App\Models\Director;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DirectorSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Director::updateOrCreate(
            ['cod_dir' => 'DIR_0001'],
            [
                'cod_pin' => 'PIN_0004',
                'est_dir' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}