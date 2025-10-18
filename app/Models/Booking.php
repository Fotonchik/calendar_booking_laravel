<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id', 
        'customer_name', 
        'customer_phone', 
        'start_time', 
        'end_time'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}