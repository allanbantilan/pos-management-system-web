<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;
use Throwable;

trait InteractsWithDateRange
{
    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveDateRange(?array $filters, int $defaultDays = 30): array
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(max($defaultDays - 1, 0));

        if (is_array($filters)) {
            $startDate = $this->parseDate($filters['startDate'] ?? null, $startDate);
            $endDate = $this->parseDate($filters['endDate'] ?? null, $endDate);
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$startDate->toDateString(), $endDate->toDateString()];
    }

    protected function parseDate(mixed $value, Carbon $fallback): Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return $fallback;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return $fallback;
        }
    }
}
