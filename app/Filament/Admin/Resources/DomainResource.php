<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DomainResource\Pages;
use App\Models\Domain;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    /**
     * This removes the Domains menu item from the navigation if there are no
     * tenants created for this user, to prevent confusion and some errors.
     * We'll apply this to all resources to keep the navigation clean.
     */
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->autofocus()
                    ->required()
                    ->helperText('Enter the domain name (without the "http://" or "https://"). Example: "example.com"'),

                Forms\Components\Select::make('tenant_id')->options(
                    Tenant::all()
                        ->pluck('name', 'id'))
                    ->default(function () {
                        return Tenant::first()->id ?? null;
                    })->required()
                    ->label('Instance Name')
                    ->helperText('A domain can only point to one instance.'),

                Forms\Components\Checkbox::make('is_subdomain')
                    ->label('Is this a subdomain?')
                    ->default(true)
                    ->helperText("If checked, we'll automatically add \".".Domain::getBaseDomain().'" to the end of the domain name.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_subdomain')
                    ->label('Subdomain?')
                    ->sortable()
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDomains::route('/'),
            'create' => Pages\CreateDomain::route('/create'),
            'edit'   => Pages\EditDomain::route('/{record}/edit'),
        ];
    }
}
