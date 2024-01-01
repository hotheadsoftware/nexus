<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Users cannot create brands. These are created in the backend when a new tenant is created.
     * Users can only edit the brand's attributes.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Users can't create or delete brands, these are associated with Tenants (Instances)
     * and managed by our back-end. Users can only edit specific brand attributes.
     */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Brand Details')
                    ->description('Select your colors & branding for this panel.')
                    ->schema([
                        Forms\Components\Toggle::make('allow_registration')->autofocus()->required(),
                        Forms\Components\TextInput::make('name')->autofocus()->required(),
                        Forms\Components\TextInput::make('headline')->autofocus()->required(),
                        Forms\Components\TextInput::make('panel')->autofocus()->required(),
                    ]),
                Forms\Components\Section::make('Brand Colors')
                    ->schema([
                        Forms\Components\ColorPicker::make('colors.manage.danger')->hexColor()->live()->default(Color::Red['500']),
                        Forms\Components\ColorPicker::make('colors.manage.primary')->hexColor()->live()->default(Color::Amber['500']),
                        Forms\Components\ColorPicker::make('colors.manage.info')->hexColor()->live()->default(Color::Sky['500']),
                        Forms\Components\ColorPicker::make('colors.manage.success')->hexColor()->live()->default(Color::Green['500']),
                        Forms\Components\ColorPicker::make('colors.manage.warning')->hexColor()->live()->default(Color::Orange['500']),
                        Forms\Components\ColorPicker::make('colors.manage.gray')->hexColor()->live()->default(Color::Slate['500']),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Brand Name'),
                Tables\Columns\TextColumn::make('tenant.name')->searchable()->sortable()->label('Instance Name'),
                Tables\Columns\TextColumn::make('headline')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('panel')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('allow_registration')->searchable()->sortable()->boolean()->alignCenter()->label('Registration Open'),
                Tables\Columns\TextColumn::make('created_at')->searchable()->sortable(),
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
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
