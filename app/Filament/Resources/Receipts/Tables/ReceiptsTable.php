<?php

namespace App\Filament\Resources\Receipts\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Receipt ID')
                    ->sortable(),
                TextColumn::make('receipt_number')
                    ->label('Receipt #')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->formatStateUsing(fn (string $state): string => $state === 'maya_checkout' ? 'PayMaya (Card/E-Wallet)' : 'Cash')
                    ->badge(),
                TextColumn::make('provider_payment_id')
                    ->label('Maya Transaction ID')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('provider_reference')
                    ->label('Maya RRN/Reference')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('issued_at')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'maya_checkout' => 'PayMaya (Card/Wallet)',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('issued_at', 'desc');
    }
}

