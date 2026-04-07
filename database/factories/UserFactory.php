<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();

        DB::table('roles')->upsert([
            [
                'kode' => 'pegawai',
                'nama' => 'Pegawai',
                'deskripsi' => 'Melihat jadwal dan mengajukan dinas luar.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['kode'], ['nama', 'deskripsi', 'updated_at']);

        $roleId = DB::table('roles')->where('kode', 'pegawai')->value('id');

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role_id' => $roleId,
            'pegawai_id' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
