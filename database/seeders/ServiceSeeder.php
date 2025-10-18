<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            ['name' => 'Поездка на квадроцикле 30 минут', 'duration' => 30],
            ['name' => 'Поездка на квадроцикле 60 минут', 'duration' => 60],
            ['name' => 'Тур на эндуро 60 минут', 'duration' => 60],
            ['name' => 'Тур на эндуро 120 минут', 'duration' => 120],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}