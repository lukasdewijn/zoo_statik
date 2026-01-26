<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'label',
        'start_time',
        'end_time',
        'active',
    ];


    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'timeslot_id');
    }

    public function capacities()
    {
        return $this-> hasMany(TimeSlotCapacity::class);
    }
}
