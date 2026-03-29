<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('log_name')
                ->label('Category')
                ->badge(),
            TextEntry::make('description')
                ->label('Description'),
            TextEntry::make('event')
                ->label('Action')
                ->badge()
                ->color(fn (?string $state): string => match ($state) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    default => 'gray',
                }),
            TextEntry::make('subject_type')
                ->label('Subject Type')
                ->formatStateUsing(fn (?string $state): string => $state ? Str::afterLast($state, '\\') : '-'),
            TextEntry::make('subject_id')
                ->label('Subject ID'),
            TextEntry::make('causer.name')
                ->label('Performed By')
                ->placeholder('System'),
            TextEntry::make('created_at')
                ->label('Date')
                ->dateTime('M d, Y h:i A'),
            KeyValueEntry::make('properties.old')
                ->label('Old Values')
                ->visible(fn ($record): bool => !empty($record?->properties['old'] ?? null)),
            KeyValueEntry::make('properties.attributes')
                ->label('New Values')
                ->visible(fn ($record): bool => !empty($record?->properties['attributes'] ?? null)),
        ]);
    }
}
