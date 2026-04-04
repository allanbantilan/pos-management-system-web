<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Exports\TransactionsExport;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Services\PdfReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

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
                    $format = $data['format'];
                    $fromDate = Carbon::parse($from)->toDateString();
                    $toDate = Carbon::parse($to)->toDateString();

                    if ($format === 'pdf') {
                        $response = app(PdfReportService::class)->generateTransactionReport($from, $to);
                    } else {
                        $export = new TransactionsExport($from, $to);
                        $response = $export->download("transactions-{$from}-to-{$to}.csv");
                    }

                    activity()
                        ->useLog('exports')
                        ->event('exported')
                        ->withProperties([
                            'resource' => 'transactions',
                            'format' => $format,
                            'date_from' => $fromDate,
                            'date_to' => $toDate,
                            'user_id' => Auth::id(),
                        ])
                        ->log('Exported Transactions (' . strtoupper($format) . ')');

                    return $response;
                }),
        ];
    }
}
