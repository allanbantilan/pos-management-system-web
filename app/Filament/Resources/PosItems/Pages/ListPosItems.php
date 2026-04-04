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
                            'pdf' => 'PDF',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    abort_unless(Auth::user()?->can('can export data'), 403);

                    if ($data['format'] === 'pdf') {
                        $response = app(PdfReportService::class)->generateInventoryReport();
                    } else {
                        $export = new PosItemsExport();
                        $date = now()->format('Y-m-d');

                        $response = $export->download("inventory-{$date}.csv");
                    }
                    activity()
                        ->useLog('exports')
                        ->event('exported')
                        ->withProperties([
                            'resource' => 'inventory',
                            'format' => $data['format'],
                            'date_from' => null,
                            'date_to' => null,
                            'user_id' => Auth::id(),
                        ])
                        ->log('Exported Inventory (' . strtoupper($data['format']) . ')');

                    return $response;
                }),
            CreateAction::make(),
        ];
    }
}
