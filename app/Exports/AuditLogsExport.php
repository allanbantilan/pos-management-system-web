<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Spatie\Activitylog\Models\Activity;

class AuditLogsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo = null,
    ) {}

    public function query(): Builder
    {
        $query = Activity::query()->with(['causer']);

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Category',
            'Description',
            'Subject',
            'Subject ID',
            'Performed By',
            'Action',
            'Date',
        ];
    }

    public function map($activity): array
    {
        return [
            $this->sanitize($activity->log_name),
            $this->sanitize($activity->description),
            $this->sanitize(class_basename($activity->subject_type ?? '')),
            $this->sanitize($activity->subject_id),
            $this->sanitize($activity->causer?->name ?? 'System'),
            $this->sanitize($activity->event),
            $activity->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function sanitize(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        $string = (string) $value;

        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = iconv('UTF-8', 'UTF-8//IGNORE', $string) ?: '';
        }

        return $string;
    }
}
