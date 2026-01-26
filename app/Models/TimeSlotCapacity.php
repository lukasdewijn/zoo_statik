<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TimeSlotCapacity extends Model
{
    protected $fillable = [
      'date',
      'time_slot_id',
      'capacity',
    ];

    protected $casts = [
        'date' => 'date',
    ];
    public function timeSlot()
    {
    return $this->belongsTo(TimeSlot::class);
    }
}
