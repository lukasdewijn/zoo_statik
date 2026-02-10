<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'recurring',
    ];

    protected $appends = ['label'];

    protected function label(): Attribute
    {
        return Attribute::get(fn () => "{$this->start_time} - {$this->end_time}");
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function capacities()
    {
        return $this->hasMany(TimeSlotCapacity::class);
    }

    public function hasFutureReservations(): bool
    {
        return $this->reservations()
            ->where('date', '>=', now()->toDateString())
            ->where('status', '!=', ReservationStatus::Cancelled)
            ->exists();
    }
}
