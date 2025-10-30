<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Batik Tulis',
            'Batik Kombinasi',
            'Batik Cetak',
        ];

        foreach ($types as $type) {
            Type::create(['type_name' => $type]);
        }
    }
}
