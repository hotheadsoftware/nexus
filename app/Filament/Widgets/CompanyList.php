<?php

namespace App\Filament\Widgets;

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

    /**
     * Widget Layout & Order
     */
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 2;


    public function table(Table $table): Table
    {
        return $table
            ->query(Tenant::where('user_id', auth()->user()->id)
                ->with('domains')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('domains')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->searchable()->sortable(),
            ]);
    }
}
