<?php

namespace App\Filament\Resources;

use App\Facades\Colors;
use App\Features\CustomBranding;
use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Laravel\Pennant\Feature;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 3;

    /**
     * This removes the Brands menu item from the navigation if there are no
     * tenants created for this user, to prevent confusion and some errors.
     * We'll apply this to all resources to keep the navigation clean.
     */
    public static function canViewAny(): bool
    {
        return Auth::user()?->tenants->count() > 0;
    }

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
        return $form->schema(array_merge(
            static::getDetailsSection(),
            static::getColorSection(),
        ));
    }

    public static function getDetailsSection(): array
    {
        $details = [
            'name'        => 'Brand Details',
            'description' => 'Select your colors & branding for this panel.',
            'schema'      => [
                Forms\Components\Toggle::make('allow_registration')->autofocus()->required(),
                Forms\Components\TextInput::make('name')->autofocus()->required(),
                Forms\Components\TextInput::make('headline')->autofocus()->required(),
                Forms\Components\TextInput::make('panel')->autofocus()->required(),
            ],
        ];

        if (Feature::active(CustomBranding::class)) {
            $details['schema'][] = Forms\Components\SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->autofocus();
        }

        return [
            Forms\Components\Section::make($details['name'])
                ->description($details['description'])
                ->schema($details['schema']),
        ];
    }

    public static function getColorSection(): array
    {
        if (! Feature::active(CustomBranding::class)) {
            return [];
        }

        $conditions    = Colors::getPanelColors();
        $color_pickers = [];
        foreach ($conditions as $condition => $color) {
            $color_pickers[] = Forms\Components\ColorPicker::make("colors.$condition")
                ->hexColor()
                ->live()
                ->default($color['500']);
        }

        return [
            Forms\Components\Section::make('Brand Colors')->schema($color_pickers)->columns(2),
        ];

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
                Tables\Columns\SpatieMediaLibraryImageColumn::make('logo')->collection('logo')->label('Logo'),
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
