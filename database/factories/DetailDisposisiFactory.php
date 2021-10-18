<?php

namespace Database\Factories;

use App\Models\DetailDisposisi;
use App\Models\Disposisi;
use App\Models\Model;
use App\Models\SuratMasuk;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailDisposisiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DetailDisposisi::class;
    protected $option = ["Sedang Diproses", "Belum Diproses", "Ditinjau", "Revisi", "Selesai"];
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_update_status' => false,
            'keterangan' => $this->faker->sentence(3),
            'created_at' => Carbon::now(),
        ];
    }

    public function has_update_status($status)
    {
        return $this->state(function (array $attributes) use ($status) {

            return [
                'is_update_status' => true,
                'keterangan' => $status,
            ];
        });
    }
}
