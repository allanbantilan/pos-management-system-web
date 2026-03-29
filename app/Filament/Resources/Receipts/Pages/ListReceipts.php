<?php

namespace App\Filament\Resources\Receipts\Pages;

use App\Exports\ReceiptsExport;
use App\Filament\Resources\Receipts\ReceiptResource;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
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
                            'xlsx' => 'Excel (XLSX)',
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
                    $export = new ReceiptsExport($from, $to);

                    return $data['format'] === 'csv'
                        ? $export->download("receipts-{$from}-to-{$to}.csv")
                        : $export->download("receipts-{$from}-to-{$to}.xlsx");
                }),
        ];
    }
}

