<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservation extends Model
{
    protected $fillable = [
        'public_code',
        'date',
        'timeslot_id',
        'contact_email',
        'status',
        'cancelled_at'
    ];

    public function visitors()
    {
        return $this->hasMany(\App\Models\Visitor::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(\App\Models\TimeSlot::class, 'timeslot_id');
    }

    protected $casts = ['date' => 'date'];

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            if(empty($reservation->public_code)) {
                $reservation->public_code = (string) Str::uuid();
            }
        });
    }
}
