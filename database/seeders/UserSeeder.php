<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(10)
            ->has(
                Contact::factory()
                    ->count(4)
                    ->state(new Sequence(
                        ['type' => 'sms', 'priority' => true, 'value' => fn() => fake()->unique()->phoneNumber()],
                        ['type' => 'sms', 'priority' => null, 'value' => fn() => fake()->unique()->phoneNumber()],
                        ['type' => 'email', 'priority' => true, 'value' => fn() => fake()->unique()->safeEmail()],
                        ['type' => 'email', 'priority' => null, 'value' => fn() => fake()->unique()->safeEmail()],
                    ))
            )
            ->create();
    }
}
