<?php

namespace Database\Factories;

use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName,
            // 'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            // 'remember_token' => Str::random(10),
            "nama"=> $this->faker->name,
            "nip"=> $this->faker->numerify("##################"),
            "no_telpon"=> $this->faker->numerify("08###########"),
            "id_jabatan"=> $this->faker->numberBetween(1,4),
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (User $user) {

        })->afterCreating(function (User $user) {

        });
    }
}
