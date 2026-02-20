<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('guard_name')
                    ->required()
                    ->live()
                    ->options([
                        'web' => 'Web',
                        'backend' => 'Backend',
                    ]),
                CheckboxList::make('permissions_by_model.user')
                    ->label('User Policy')
                    ->options(fn (Get $get): array => self::permissionOptionsByGroup($get('guard_name') ?? 'web', 'user'))
                    ->columns(2)
                    ->bulkToggleable(),
                CheckboxList::make('permissions_by_model.backend_user')
                    ->label('Backend User Policy')
                    ->options(fn (Get $get): array => self::permissionOptionsByGroup($get('guard_name') ?? 'web', 'backend_user'))
                    ->visible(fn (Get $get): bool => ($get('guard_name') ?? 'web') === 'backend')
                    ->columns(2)
                    ->bulkToggleable(),
                CheckboxList::make('permissions_by_model.pos_item')
                    ->label('POS Item Policy')
                    ->options(fn (Get $get): array => self::permissionOptionsByGroup($get('guard_name') ?? 'web', 'pos_item'))
                    ->columns(2)
                    ->bulkToggleable(),
                CheckboxList::make('permissions_by_model.pos_category')
                    ->label('POS Category Policy')
                    ->options(fn (Get $get): array => self::permissionOptionsByGroup($get('guard_name') ?? 'web', 'pos_category'))
                    ->columns(2)
                    ->bulkToggleable(),
                CheckboxList::make('permissions_by_model.role')
                    ->label('Role Policy')
                    ->options(fn (Get $get): array => self::permissionOptionsByGroup($get('guard_name') ?? 'web', 'role'))
                    ->columns(2)
                    ->bulkToggleable(),
                CheckboxList::make('permissions_by_model.general')
                    ->label('General Permissions')
                    ->options(fn (Get $get): array => self::permissionOptionsByGroup($get('guard_name') ?? 'web', 'general'))
                    ->columns(2)
                    ->bulkToggleable()
                    ->helperText('Check each permission to attach to this role.'),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function permissionOptionsByGroup(string $guard, string $group): array
    {
        return Permission::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->get()
            ->filter(fn (Permission $permission): bool => self::permissionGroupKey($permission->name) === $group)
            ->mapWithKeys(fn (Permission $permission): array => [(string) $permission->id => $permission->name])
            ->all();
    }

    public static function permissionGroupKey(string $permission): string
    {
        if (str_ends_with($permission, 'backend user')) {
            return 'backend_user';
        }

        if (str_ends_with($permission, 'pos category')) {
            return 'pos_category';
        }

        if (str_ends_with($permission, 'pos item')) {
            return 'pos_item';
        }

        if (str_ends_with($permission, 'user')) {
            return 'user';
        }

        if (str_ends_with($permission, 'role')) {
            return 'role';
        }

        return 'general';
    }
}
