<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('reservations:send-reminders')->dailyAt('12:00');
Schedule::command('reservations:cleanup-expired')->dailyAt('01:00');
Schedule::command('timeslots:generate-capacities')->dailyAt('00:00');
Schedule::command('analytics:daily-report')->dailyAt('20:00');
