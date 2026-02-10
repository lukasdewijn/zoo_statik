<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'public_code',
        'date',
        'time_slot_id',
        'contact_email',
        'status',
        'cancelled_at',
    ];

    public function visitors()
    {
        return $this->hasMany(\App\Models\Visitor::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(\App\Models\TimeSlot::class);
    }


    protected $casts = [
        'date' => 'date',
        'status' => ReservationStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            if (empty($reservation->public_code)) {
                $reservation->public_code = (string) Str::uuid();
            }
        });
    }
}
