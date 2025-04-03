<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $totalUsers = 10;
        $merchantPercentage = 0.3;
        $merchantCount = round($totalUsers * $merchantPercentage);
        $commonCount = $totalUsers - $merchantCount;

        for ($i = 0; $i < $commonCount; $i++) {
            User::create([
                'name' => $faker->name,
                'cpf' => $faker->cpf(false),
                'cnpj' => null,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('12345'),
                'user_type' => 'common',
                'balance' => $faker->randomFloat(2, 0, 1000),
            ]);
        }

        for ($i = 0; $i < $merchantCount; $i++) {
            User::create([
                'name' => $faker->company,
                'cpf' => null,
                'cnpj' => $faker->cnpj(false),
                'email' => $faker->unique()->companyEmail,
                'password' => Hash::make('12345'),
                'user_type' => 'merchant',
                'balance' => $faker->randomFloat(2, 0, 1000),
            ]);
        }
    }
}
