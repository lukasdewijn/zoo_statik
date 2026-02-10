<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class VisitorsAnalytics extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Visitors graph';

    protected static ?string $title = 'Visitors per day';

    protected string $view = 'filament.pages.visitors-analytics';

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from = now()->subDays(30)->toDateString();
        $this->to = now()->addDays(30)->toDateString();
    }

    public function updatedFrom(): void
    {
        $this->ensureValidRange();
    }

    public function updatedTo(): void
    {
        $this->ensureValidRange();
    }

    private function ensureValidRange(): void
    {
        if ($this->from && $this->to && $this->from > $this->to) {
            [$this->from, $this->to] = [$this->to, $this->from];
        }
    }
}
