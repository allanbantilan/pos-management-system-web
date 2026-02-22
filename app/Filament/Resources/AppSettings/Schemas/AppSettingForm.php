<?php

namespace App\Filament\Resources\AppSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AppSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pos_name')
                    ->label('POS Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Fast Food Kiosk'),
                Select::make('color_scheme')
                    ->label('Color Theme')
                    ->options(function (): array {
                        $schemes = (array) config('branding.schemes', []);

                        return collect($schemes)
                            ->mapWithKeys(fn (array $scheme, string $key) => [$key => (string) ($scheme['label'] ?? $key)])
                            ->all();
                    })
                    ->default((string) config('branding.default_scheme', 'sunset'))
                    ->required(),
                SpatieMediaLibraryFileUpload::make('logo')
                    ->label('Logo')
                    ->collection('branding-logo')
                    ->disk('public')
                    ->image()
                    ->imageEditor()
                    ->maxFiles(1),
            ])
            ->columns(2);
    }
}
