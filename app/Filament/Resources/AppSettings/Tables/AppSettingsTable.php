<?php

namespace App\Filament\Resources\AppSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pos_name')
                    ->label('POS Name'),
                TextColumn::make('color_scheme')
                    ->label('Color Theme')
                    ->formatStateUsing(function (?string $state): string {
                        $schemes = (array) config('branding.schemes', []);

                        return (string) ($schemes[$state ?? '']['label'] ?? $state ?? '');
                    }),
                IconColumn::make('logo')
                    ->label('Logo')
                    ->boolean()
                    ->state(fn ($record): bool => $record->hasMedia('branding-logo')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
