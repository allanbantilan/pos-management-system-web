<?php

namespace App\Filament\Resources\PosItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PosItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('')
                    ->collection('item-images')
                    ->conversion('thumb')
                    ->circular(),
                TextColumn::make('name')
                    ->description(fn ($record): string => $record->sku)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('On Hand')
                    ->numeric()
                    ->badge()
                    ->color(fn ($record): string => $record->stock <= $record->min_stock ? 'danger' : 'success')
                    ->description(fn ($record): string => "Reorder at {$record->min_stock}")
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Available')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(fn () => \App\Models\PosCategory::query()->orderBy('sort_order')->pluck('name', 'name')->all()),
                TrashedFilter::make(),
            ])
            ->striped()
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
