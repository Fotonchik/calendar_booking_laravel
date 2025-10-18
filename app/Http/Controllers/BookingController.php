<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Booking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index()
    {
        $services = Service::all();
        
        return Inertia::render('Home', [
            'services' => $services,
        ]);
    }

    // Проверка доступности дней недели
    public function getWeekAvailability(Request $request)
    {
    $request->validate([
        'service_id' => 'required|exists:services,id',
        'week_start' => 'required|date'
    ]);

    $service = Service::findOrFail($request->service_id);
    $weekStart = Carbon::parse($request->week_start);

    $weekDays = [];

    // Генерируем 7 дней недели
    for ($i = 0; $i < 7; $i++) {
        $currentDate = $weekStart->copy()->addDays($i);
        
        $isAvailable = $this->isDateAvailable($service, $currentDate);
        $isFullyBooked = $this->isDayFullyBooked($service, $currentDate);
        
        $weekDays[] = [
            'date' => $currentDate->format('Y-m-d'),
            'is_available' => $isAvailable && !$isFullyBooked,
            'is_sunday' => $currentDate->isSunday(),
            'is_past' => $currentDate->lt(now()->startOfDay()),
            'is_fully_booked' => $isFullyBooked
        ];
    }

    return response()->json(['week_days' => $weekDays]);
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date'
        ]);

        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);
        
        // Проверяем, что день не воскресенье
        if ($date->isSunday()) {
            return response()->json([
                'slots' => [],
                'message' => 'Воскресенье - выходной день'
            ]);
        }

        // Проверяем, что дата не в прошлом
        if ($date->lt(now()->startOfDay())) {
            return response()->json([
                'slots' => [],
                'message' => 'Нельзя выбрать прошедшую дату'
            ]);
        }

        // Проверяем доступность дня
        if (!$this->isDateAvailable($service, $date)) {
            return response()->json([
                'slots' => [],
                'message' => 'На этот день нет доступных слотов'
            ]);
        }

        // рабочие часы 
        $workingHoursStart = 10;
        $workingHoursEnd = 20;
        $bookings = Booking::where('service_id', $service->id)
            ->whereDate('start_time', $date->format('Y-m-d'))
            ->get();

        // Генерир слоты
        $allSlots = $this->generateTimeSlots($workingHoursStart, $workingHoursEnd);
        $availableSlots = [];

        foreach ($allSlots as $slotTime) {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $slotTime);
            $slotEnd = $slotStart->copy()->addMinutes($service->duration + 30);
            $slotEndInMsk = $slotEnd->copy()->timezone('Europe/Moscow');
            if ($slotEndInMsk->hour >= $workingHoursEnd) {
                continue;
            }
            // пересечения с существующими бронированиями
            $hasConflict = $this->hasTimeConflict($slotStart, $slotEnd, $bookings);
            
            if (!$hasConflict) {
                $availableSlots[] = [
                    'time' => $slotTime,
                    'formattedTime' => $slotStart->format('H:i'),
                    'endTime' => $slotEnd->format('H:i')
                ];
            }
        }

        return response()->json([
            'slots' => $availableSlots,
            'message' => count($availableSlots) > 0 ? 'Доступные слоты загружены' : 'Нет доступных слотов'
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20'
        ]);

        return DB::transaction(function () use ($request) {
            $service = Service::findOrFail($request->service_id);
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
            // время не в прошлом
            if ($startTime->lt(now())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Нельзя забронировать прошедшее время'
                ], 422);
            }

            // не воскресенье
            if ($startTime->isSunday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Бронирование в воскресенье невозможно'
                ], 422);
            }

            $validationResult = $this->validateBookingTime($service, $startTime);
            if (!$validationResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message']
                ], 422);
            }

            // Двойная проверка на случай race condition!!!!
            $conflictingBooking = $this->checkForConflictingBookings($service, $startTime);

            if ($conflictingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'К сожалению, это время только что заняли. Пожалуйста, выберите другое время.'
                ], 422);
            }

            // Создаем бронирование
            $endTime = $startTime->copy()->addMinutes($service->duration + 30);
            
            $booking = Booking::create([
                'service_id' => $service->id,
                'customer_name' => $request->name,
                'customer_phone' => $request->phone,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Бронирование успешно создано!',
                'booking' => [
                    'id' => $booking->id,
                    'service' => $service->name,
                    'date' => $startTime->format('d.m.Y'),
                    'time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'customer' => $booking->customer_name,
                    'phone' => $booking->customer_phone,
                    'duration' => $service->duration + 30
                ]
            ]);
        });
    }

    private function isDateAvailable(Service $service, Carbon $date): bool
    {
        // Воскресенье всегда недоступно
        if ($date->isSunday()) {
            return false;
        }
        // Прошедшие даты недоступны
        if ($date->lt(now()->startOfDay())) {
            return false;
        }

        // все бронирования на эту дату
        $bookings = Booking::where('service_id', $service->id)
            ->whereDate('start_time', $date->format('Y-m-d'))
            ->get();
        $workingHoursStart = 10;
        $workingHoursEnd = 20;
        $allSlots = $this->generateTimeSlots($workingHoursStart, $workingHoursEnd);

        $hasAvailableSlots = false;

        // один доступный слот - вперед
        foreach ($allSlots as $slotTime) {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $slotTime);
            $slotEnd = $slotStart->copy()->addMinutes($service->duration + 30);
            $slotEndInMsk = $slotEnd->copy()->timezone('Europe/Moscow');
            if ($slotEndInMsk->hour >= $workingHoursEnd) {
                continue;
            }
            $hasConflict = $this->hasTimeConflict($slotStart, $slotEnd, $bookings);
            
            if (!$hasConflict) {
                $hasAvailableSlots = true;
                break;
            }
        }

        return $hasAvailableSlots;
    }
    // Проверка полной занятости дня
    private function isDayFullyBooked(Service $service, Carbon $date): bool
    {
        if ($date->isSunday() || $date->lt(now()->startOfDay())) {
            return true;
        }

        $bookings = Booking::where('service_id', $service->id)
            ->whereDate('start_time', $date->format('Y-m-d'))
            ->get();

        $workingHoursStart = 10;
        $workingHoursEnd = 20;
        $allSlots = $this->generateTimeSlots($workingHoursStart, $workingHoursEnd);

        $availableSlotsCount = 0;

        foreach ($allSlots as $slotTime) {
            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $slotTime);
            $slotEnd = $slotStart->copy()->addMinutes($service->duration + 30);
            $slotEndInMsk = $slotEnd->copy()->timezone('Europe/Moscow');
            if ($slotEndInMsk->hour >= $workingHoursEnd) {
                continue;
            }
            $hasConflict = $this->hasTimeConflict($slotStart, $slotEnd, $bookings);
            if (!$hasConflict) {$availableSlotsCount++;}
        }

        return $availableSlotsCount === 0;
    }
    
    private function generateTimeSlots($startHour, $endHour): array
    {
        $slots = [];
        $current = Carbon::createFromTime($startHour, 0, 0, 'Europe/Moscow');
        $end = Carbon::createFromTime($endHour, 0, 0, 'Europe/Moscow');

        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->addMinutes(30);
        }

        return $slots;
    }

    /**
     * Проверяет конфликт времени
     */
    private function hasTimeConflict(Carbon $slotStart, Carbon $slotEnd, $bookings): bool
    {
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_time);
            $bookingEnd = Carbon::parse($booking->end_time);

            // Проверяем пересечение интервалов
            if ($slotStart < $bookingEnd && $slotEnd > $bookingStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * Валидация времени бронирования
     */
    private function validateBookingTime(Service $service, Carbon $startTime): array
    {
        $mskStart = $startTime->copy()->timezone('Europe/Moscow');
        $endTime = $startTime->copy()->addMinutes($service->duration + 30);
        $mskEnd = $endTime->copy()->timezone('Europe/Moscow');

        if ($mskStart->hour < 10 || $mskEnd->hour > 20 || ($mskEnd->hour == 20 && $mskEnd->minute > 0)) {
            return [
                'success' => false, 
                'message' => 'Время бронирования должно быть в интервале с 10:00 до 20:00 по московскому времени'
            ];
        }

        return ['success' => true];
    }

    /**
     * Проверка конфликтующих бронирований с блокировкой
     */
    private function checkForConflictingBookings(Service $service, Carbon $startTime)
    {
        $endTime = $startTime->copy()->addMinutes($service->duration + 30);

        return Booking::where('service_id', $service->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime->copy()->subSecond()])
                    ->orWhereBetween('end_time', [$startTime->copy()->addSecond(), $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $startTime)
                          ->where('end_time', '>', $endTime);
                    });
            })
            ->lockForUpdate() // ЗАЩИТА ОТ RACE CONDITION
            ->first();
    }
}