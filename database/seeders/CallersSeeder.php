<?php

namespace Database\Seeders;

use App\Enums\CallerGender;
use App\Models\Caller;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Caller::insert([
            [
                'name' => 'Sarah',
                'gender' => CallerGender::FEMALE,
                'xi_voice_id' => 'XrExE9yKIg1WjnnlVkGX',
            ],

            [
                'name' => 'Amy',
                'gender' => CallerGender::FEMALE,
                'xi_voice_id' => 'rQLtUecIltZfNYorYhlF',
            ],

            [
                'name' => 'Jason',
                'gender' => CallerGender::MALE,
                'xi_voice_id' => 'GkGwJTC9k7mH9ILkptZy',
            ],

            [
                'name' => 'Adam',
                'gender' => CallerGender::MALE,
                'xi_voice_id' => 'JBFqnCBsd6RMkjVDRZzb',
            ],
        ]);
    }
}
