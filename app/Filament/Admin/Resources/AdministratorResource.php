<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdministratorResource\Pages;
use App\Models\Administrator;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hash;
use Illuminate\Database\Eloquent\Model;

/**
 * An administrator is a class of user which has access to the 'admin' Filament panel.
 * This user can pull reports and manage issues, manage users, manage tenants, and
 * manage other resources in both central and tenant contexts, as authorized.
 */
class AdministratorResource extends Resource
{
    protected static ?string $model = Administrator::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete administrators');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->autofocus()
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAdministrators::route('/'),
            'create' => Pages\CreateAdministrator::route('/create'),
            'edit'   => Pages\EditAdministrator::route('/{rec  ord}/edit'),
        ];
    }
}
