<?php

namespace App\Filament\Widgets\Account;

use App\Models\Tenant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CompanyList extends BaseWidget
{
    /**
     * How frequently the widget will poll for updates.
     */
    protected static ?string $pollingInterval = '30s';

    protected static ?string $heading = 'Instances';

    /**
     * Widget Layout & Order
     */
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(Tenant::where('user_id', auth()->user()->id)
                ->with('domains')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('domains.domain')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->searchable()->sortable(),
            ])->pluralModelLabel($this::$heading)->headerActions([
                Tables\Actions\CreateAction::make('Create Instance')
                    ->url(route('filament.account.resources.instances.create'))
                    ->icon('heroicon-o-plus-circle'),
            ]);
    }
}
