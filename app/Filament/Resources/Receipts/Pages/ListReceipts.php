<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Exports\ReceiptsExport;
use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListReceipts extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

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
                    DatePicker::make('date_from')
                        ->label('From')
                        ->required(),
                    DatePicker::make('date_to')
                        ->label('To')
                        ->required(),
                ])
                ->action(function (array $data) {
                    abort_unless(Auth::user()?->can('can export data'), 403);

                    $from = $data['date_from'];
                    $to = $data['date_to'];

                    if (Carbon::parse($from)->gt(Carbon::parse($to))) {
                        [$from, $to] = [$to, $from];
                    }

                    $fromDate = Carbon::parse($from)->toDateString();
                    $toDate = Carbon::parse($to)->toDateString();

                    if ($data['format'] === 'pdf') {
                        $response = app(\App\Services\PdfReportService::class)->generateReceiptsReport($from, $to);
                    } else {
                        $export = new ReceiptsExport($from, $to);
                        $response = $export->download("receipts-{$from}-to-{$to}.csv");
                    }

                    activity()
                        ->useLog('exports')
                        ->event('exported')
                        ->withProperties([
                            'resource' => 'receipts',
                            'format' => $data['format'],
                            'date_from' => $fromDate,
                            'date_to' => $toDate,
                            'user_id' => Auth::id(),
                        ])
                        ->log('Exported Receipts (' . strtoupper($data['format']) . ')');

                    return $response;
                }),
        ];
    }
}
