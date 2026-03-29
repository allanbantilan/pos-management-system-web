<?php

namespace App\Filament\Resources\PosItems\Pages;

use App\Exports\PosItemsExport;
use App\Filament\Resources\PosItems\PosItemResource;
use App\Services\PdfReportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListPosItems extends ListRecords
{
    protected static string $resource = PosItemResource::class;

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
                ])
                ->action(function (array $data) {
                    if ($data['format'] === 'pdf') {
                        return app(PdfReportService::class)->generateInventoryReport();
                    }

                    $export = new PosItemsExport();
                    $date = now()->format('Y-m-d');

                    return $data['format'] === 'csv'
                        ? $export->download("inventory-{$date}.csv")
                        : $export->download("inventory-{$date}.xlsx");
                }),
            CreateAction::make(),
        ];
    }
}
