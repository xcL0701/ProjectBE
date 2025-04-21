<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\BadgeColumn;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('amount')->numeric()->required(),
            Forms\Components\TextInput::make('proof')->required(),
            Forms\Components\DatePicker::make('paid_at')->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('amount')->money('IDR'),
            Tables\Columns\ImageColumn::make('proof'),
            TextColumn::make('paid_at')->date(),
            BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
        ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Payments';
    }
}
