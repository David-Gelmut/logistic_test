<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::factory()
            ->count(10000)
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


        /*$user1 = User::create(['name' => 'Test User 1',
            'email' => 'davidgelmut12345@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10)]);

        $user1->contacts()->createMany([
            ['type' => 'sms', 'value' => '+79669871231'],
            ['type' => 'sms', 'value' => '+79669871232'],
            ['type' => 'email', 'value' => 'davidgelmut12345@gmail.com']
        ]);

        $user2 = User::create(['name' => 'Test User 2',
            'email' => 'david-gelmut@rambler.ru',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        $user2->contacts()->createMany([
            ['type' => 'sms', 'value' => '+79669871233'],
            ['type' => 'sms', 'value' => '+79669871234'],
            ['type' => 'email', 'value' => 'david-gelmut@rambler.ru']
        ]);*/
    }
}
