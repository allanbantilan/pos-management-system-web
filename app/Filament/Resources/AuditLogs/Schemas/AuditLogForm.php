<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        $buildChangeRows = static function ($record): array {
            $attributeChanges = $record?->attribute_changes ?? [];
            $properties = $record?->properties ?? [];

            $old = $attributeChanges['old'] ?? $properties['old'] ?? [];
            $new = $attributeChanges['attributes'] ?? $properties['attributes'] ?? [];

            $keys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
            sort($keys);

            $stringify = static function (mixed $value): string {
                if ($value === null) {
                    return '-';
                }

                if (is_array($value)) {
                    return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '-';
                }

                return (string) $value;
            };

            return array_map(static function (string $key) use ($old, $new, $stringify): array {
                return [
                    'field' => $key,
                    'old' => $stringify($old[$key] ?? null),
                    'new' => $stringify($new[$key] ?? null),
                ];
            }, $keys);
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
            ViewEntry::make('changes')
                ->view('filament.audit-logs.changes-table', fn ($record): array => ['rows' => $buildChangeRows($record)])
                ->visible(fn ($record): bool => count($buildChangeRows($record)) > 0),
        ]);
    }
}
