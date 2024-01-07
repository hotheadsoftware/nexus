<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AdministratorResource\Pages;
use App\Models\Administrator;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * An administrator is a class of user which has access to the 'admin' Filament panel.
 * This user can pull reports and manage issues, manage users, manage tenants, and
 * manage other resources in both central and tenant contexts, as authorized.
 */
class AdministratorResource extends Resource
{
    protected static ?string $model = Administrator::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'edit'   => Pages\EditAdministrator::route('/{record}/edit'),
        ];
    }
}
