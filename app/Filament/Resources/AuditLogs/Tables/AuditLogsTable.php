<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('log_name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::afterLast($state, '\\') : '-')
                    ->toggleable(),
                TextColumn::make('subject_id')
                    ->label('Subject ID')
                    ->toggleable(),
                TextColumn::make('causer.name')
                    ->label('Performed By')
                    ->placeholder('System')
                    ->searchable(),
                TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Category')
                    ->options([
                        'pos-items' => 'Products',
                        'pos-categories' => 'Categories',
                        'transactions' => 'Transactions',
                        'app-settings' => 'App Settings',
                        'users' => 'Users',
                        'backend-users' => 'Backend Users',
                    ]),
                SelectFilter::make('event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
