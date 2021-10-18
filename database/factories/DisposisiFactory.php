<?php

namespace Database\Factories;

use App\Models\Disposisi;
use App\Models\Model;
use App\Models\SuratMasuk;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisposisiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Disposisi::class;
    protected $option = ["Sedang Diproses", "Belum Diproses", "Ditinjau", "Revisi", "Selesai"];
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_suratmasuk' => false,
            // 'tanggal_surat' => $this->faker->dateTimeBetween('-5 months', 'now'),
            'no_disposisi' => $this->faker->numerify('###/###/###'),
            'status' => "Belum Diproses",
            'isi' => $this->faker->sentence(1),
            'id_pengirim' => 1,
            'id_jabatan' => $this->faker->randomElement([2, 3]),
            'expired_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function is_suratmasuk()
    {
        return $this->state(function (array $attributes) {

            return [
                'is_suratmasuk' => true,
                "id_suratmasuk" => $this->faker->numberBetween(1, 100),
            ];
        });
    }

}
