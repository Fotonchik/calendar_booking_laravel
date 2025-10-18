<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->timestamps();
            
            // Индекс для быстрого поиска бронирований по времени
            $table->index(['service_id', 'start_time']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
