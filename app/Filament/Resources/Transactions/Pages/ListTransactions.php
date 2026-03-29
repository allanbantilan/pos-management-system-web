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
                            'xlsx' => 'Excel (XLSX)',
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
                    $from = $data['date_from'];
                    $to = $data['date_to'];

                    if ($data['format'] === 'pdf') {
                        return app(PdfReportService::class)->generateTransactionReport($from, $to);
                    }

                    $export = new TransactionsExport($from, $to);

                    return $data['format'] === 'csv'
                        ? $export->download("transactions-{$from}-to-{$to}.csv")
                        : $export->download("transactions-{$from}-to-{$to}.xlsx");
                }),
        ];
    }
}

