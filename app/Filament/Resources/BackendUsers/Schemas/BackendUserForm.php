<?php

namespace App\Filament\Resources\BackendUsers\Schemas;

use App\Models\BackendUser;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BackendUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                Select::make('roles')
                    ->label('POS Roles')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('guard_name', 'backend')
                    )
                    ->multiple()
                    ->preload()
                    ->required()
                    // Only super admins may change role assignments. For anyone
                    // else this field is read-only, preventing self-escalation
                    // (e.g. attaching the backend-admin role to their own account).
                    ->disabled(fn (): bool => ! (Auth::guard('backend')->user() instanceof BackendUser
                        && Auth::guard('backend')->user()->hasRole('backend-admin'))),
            ]);
    }
}
