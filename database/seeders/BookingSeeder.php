<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        $services = [
            'quadro30' => Service::where('name', 'Поездка на квадроцикле 30 минут')->first(),
            'quadro60' => Service::where('name', 'Поездка на квадроцикле 60 минут')->first(),
            'enduro60' => Service::where('name', 'Тур на эндуро 60 минут')->first(),
            'enduro120' => Service::where('name', 'Тур на эндуро 120 минут')->first(),
        ];

        $customers = [
            ['name' => 'Ада Лавлейс', 'phone' => '+73443434343'],
            ['name' => 'Алан Тьюринг', 'phone' => '+74343434334'],
            ['name' => 'Грейс Хоппер', 'phone' => '+73444343434'],
            ['name' => 'Дональд Кнут', 'phone' => '+74344343443'],
            ['name' => 'Линус Торвальдс', 'phone' => '+73443434434'],
            ['name' => 'Маргарет Гамильтон', 'phone' => '+74343443434'],
            ['name' => 'Стив Джобс', 'phone' => '+73434343444'],
            ['name' => 'Билл Гейтс', 'phone' => '+74343434343'],
            ['name' => 'Тим Бернерс-Ли', 'phone' => '+73443443443'],
            ['name' => 'Джеймс Гослинг', 'phone' => '+74344344344'],
        ];

        $dates = [
            '2025-10-16', 
            '2025-10-17', 
            '2025-10-20',
            '2025-10-21',
        ];

        $bookings = [];

        // Поездка на квадроцикле 30 минут
        $this->addBooking($bookings, $services['quadro30'], $dates[0], '13:00', $customers[0]);
        $this->addBooking($bookings, $services['quadro30'], $dates[0], '16:00', $customers[1]);
        $this->addBooking($bookings, $services['quadro30'], $dates[1], '10:00', $customers[2]);
        $this->addBooking($bookings, $services['quadro30'], $dates[1], '11:00', $customers[3]);
        $this->addBooking($bookings, $services['quadro30'], $dates[1], '13:00', $customers[4]);
        $this->addBooking($bookings, $services['quadro30'], $dates[1], '18:00', $customers[5]);
        $this->addBooking($bookings, $services['quadro30'], $dates[3], '10:00', $customers[6]);
        $this->addBooking($bookings, $services['quadro30'], $dates[3], '11:00', $customers[7]);
        $this->addBooking($bookings, $services['quadro30'], $dates[3], '13:00', $customers[8]);
        $this->addBooking($bookings, $services['quadro30'], $dates[3], '18:00', $customers[9]);

        // Поездка на квадроцикле 60 минут
        $this->addBooking($bookings, $services['quadro60'], $dates[0], '10:00', $customers[0]);
        $this->addBooking($bookings, $services['quadro60'], $dates[3], '10:00', $customers[1]);

        // Тур на эндуро 60 минут
        $this->addBooking($bookings, $services['enduro60'], $dates[0], '10:00', $customers[2]);
        $this->addBooking($bookings, $services['enduro60'], $dates[0], '11:30', $customers[3]);
        $this->addBooking($bookings, $services['enduro60'], $dates[0], '18:30', $customers[4]);
        $this->addBooking($bookings, $services['enduro60'], $dates[3], '10:00', $customers[5]);
        $this->addBooking($bookings, $services['enduro60'], $dates[3], '11:30', $customers[6]);
        $this->addBooking($bookings, $services['enduro60'], $dates[3], '18:30', $customers[7]);

        // Тур на эндуро 120 минут
        $this->addBooking($bookings, $services['enduro120'], $dates[1], '14:00', $customers[8]);
        $this->addBooking($bookings, $services['enduro120'], $dates[3], '14:00', $customers[9]);

        // Создаем бронирования
        foreach ($bookings as $booking) {
            Booking::create($booking);
        }

        $this->command->info('Создано ' . count($bookings) . ' тестовых бронирований');
    }

    // Вспомогательная функция для добавления бронирования
    private function addBooking(array &$bookings, Service $service, string $date, string $time, array $customer)
    {
        $startTime = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
        
        // Пропускаем воскресенья
        if ($startTime->isSunday()) {
            return;
        }

        $endTime = $startTime->copy()->addMinutes($service->duration + 30);

        $bookings[] = [
            'service_id' => $service->id,
            'customer_name' => $customer['name'],
            'customer_phone' => $customer['phone'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}