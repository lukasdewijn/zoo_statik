<?php

namespace App\Filament\Widgets;

use App\Enums\ReservationStatus;
use App\Models\Visitor;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class ReservationsTimeSeries extends ChartWidget
{
    protected ?string $heading = 'Visitors per day';

    // Optioneel: refresh elke 60s (default is 5s)
    protected ?string $pollingInterval = '60s';

    public ?string $from = null;

    public ?string $to = null;

    protected $listeners = [
        'analyticsRangeUpdated' => 'setRange',
    ];

    public function setRange(string $from, string $to): void
    {
        $this->from = $from;
        $this->to = $to;
        $this->dispatch('$refresh');
    }

    protected int|string|array $columnSpan = 2;

    protected function getMaxHeight(): ?string
    {
        return '500 px';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $from = $this->from ?? now()->subDays(5)->toDateString();
        $to = $this->to ?? now()->addDays(31)->toDateString();

        $counts = Visitor::query()
            ->join('reservations', 'visitors.reservation_id', '=', 'reservations.id')
            ->whereIn('reservations.status', [ReservationStatus::Confirmed, ReservationStatus::Completed])
            ->whereBetween('reservations.date', [$from, $to])
            ->selectRaw('reservations.date as day, COUNT(visitors.id) as visitor_count')
            ->groupBy('day')
            ->pluck('visitor_count', 'day');

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $labels[] = $date->format('d M');
            $data[] = (int) ($counts[$date->toDateString()] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => $data,
                    // Chart.js options can be added here
                ],
            ],
            'labels' => $labels,
        ];
    }
}
