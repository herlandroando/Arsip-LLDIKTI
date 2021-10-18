<?php

namespace Database\Seeders;

use App\Models\SifatSurat;
use Illuminate\Database\Seeder;

class SifatSuratSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $values = ['Rahasia', 'Biasa', 'Rutin'];
        foreach ($values as $value) {
            SifatSurat::create(['nama' => $value]);
        }
    }
}
