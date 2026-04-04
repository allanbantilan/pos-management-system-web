<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Exports\AuditLogsExport;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\Transaction;
use App\Services\PdfReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Icons\Heroicon;
use Spatie\Activitylog\Models\Activity;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    public function getTableRecords(): Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
    {
        /** @var Collection<int, Activity>|\\Illuminate\\Pagination\\LengthAwarePaginator $records */
        $records = parent::getTableRecords();

        if ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $grouped = $this->groupTransactionActivities($records->getCollection());
            $records->setCollection($grouped);

            return $records;
        }

        return $this->groupTransactionActivities($records);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->visible(fn () => Auth::user()?->can('can export data'))
                ->form([
                    Select::make('format')
                        ->label('Format')
                        ->options([
                            'csv' => 'CSV',
                            'pdf' => 'PDF',
                        ])
                        ->required(),
                    DatePicker::make('date_from')->label('From')->required(),
                    DatePicker::make('date_to')->label('To')->required(),
                ])
                ->action(function (array $data) {
                    abort_unless(Auth::user()?->can('can export data'), 403);

                    $from = $data['date_from'];
                    $to = $data['date_to'];

                    $fromDate = \Illuminate\Support\Carbon::parse($from)->toDateString();
                    $toDate = \Illuminate\Support\Carbon::parse($to)->toDateString();

                    if ($data['format'] === 'pdf') {
                        $response = app(PdfReportService::class)->generateAuditLogReport($from, $to);
                    } else {
                        $export = new AuditLogsExport($from, $to);
                        $response = $export->download("audit-logs-{$from}-to-{$to}.csv");
                    }

                    activity()
                        ->useLog('exports')
                        ->event('exported')
                        ->withProperties([
                            'resource' => 'audit-logs',
                            'format' => $data['format'],
                            'date_from' => $fromDate,
                            'date_to' => $toDate,
                            'user_id' => Auth::id(),
                        ])
                        ->log('Exported Audit Logs (' . strtoupper($data['format']) . ')');

                    return $response;
                }),
        ];
    }

    private function groupTransactionActivities(Collection $records): Collection
    {
        $windowSeconds = 3;
        $grouped = collect();
        $currentGroup = [];
        $last = null;

        foreach ($records as $record) {
            $isTransaction = $record->subject_type === Transaction::class && $record->subject_id !== null;

            if (! $isTransaction) {
                if (! empty($currentGroup)) {
                    $grouped->push($this->summarizeGroup($currentGroup));
                    $currentGroup = [];
                    $last = null;
                }

                $grouped->push($record);
                continue;
            }

            if ($last === null) {
                $currentGroup = [$record];
                $last = $record;
                continue;
            }

            $sameSubject = $record->subject_id === $last->subject_id;
            $closeInTime = $record->created_at->diffInSeconds($last->created_at) <= $windowSeconds;

            if ($sameSubject && $closeInTime) {
                $currentGroup[] = $record;
                $last = $record;
                continue;
            }

            $grouped->push($this->summarizeGroup($currentGroup));
            $currentGroup = [$record];
            $last = $record;
        }

        if (! empty($currentGroup)) {
            $grouped->push($this->summarizeGroup($currentGroup));
        }

        return $grouped;
    }

    /**
     * @param  array<int, Activity>  $group
     */
    private function summarizeGroup(array $group): Activity
    {
        /** @var Activity $latest */
        $latest = $group[0];

        $createdCount = collect($group)->where('event', 'created')->count();
        $updatedCount = collect($group)->where('event', 'updated')->count();

        $summary = $createdCount
            ? 'Transaction created' . ($updatedCount ? " + {$updatedCount} updates" : '')
            : 'Transaction updated (x' . count($group) . ')';

        $latest->setAttribute('grouped_description', $summary);
        $latest->setAttribute('grouped_event', $createdCount ? 'created' : 'updated');

        return $latest;
    }
}
