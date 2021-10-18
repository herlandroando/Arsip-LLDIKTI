<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfilFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profil::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "nama"=> $this->faker->name,
            "nip"=> $this->faker->numerify("##################"),
            "no_telpon"=> $this->faker->numerify("08###########"),
            "id_jabatan"=> $this->faker->numberBetween(1,4),
            "id_pengguna" => User::factory()->create()->id
        ];
    }
}
