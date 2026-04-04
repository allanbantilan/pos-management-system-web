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
        $resolveChanges = static function ($record, string $key): mixed {
            $attributeChanges = data_get($record?->attribute_changes, $key);

            if (!empty($attributeChanges)) {
                return $attributeChanges;
            }

            return data_get($record?->properties, $key);
        };

        $hasChanges = static function ($record, string $key) use ($resolveChanges): bool {
            return !empty($resolveChanges($record, $key));
        };

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
            KeyValueEntry::make('attribute_changes.old')
                ->label('Old Values')
                ->getStateUsing(fn ($record) => $resolveChanges($record, 'old'))
                ->visible(fn ($record): bool => $hasChanges($record, 'old')),
            KeyValueEntry::make('attribute_changes.attributes')
                ->label('New Values')
                ->getStateUsing(fn ($record) => $resolveChanges($record, 'attributes'))
                ->visible(fn ($record): bool => $hasChanges($record, 'attributes')),
        ]);
    }
}
