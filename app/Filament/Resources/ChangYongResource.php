<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChangYongResource\Pages;
use App\Filament\Resources\ChangYongResource\RelationManagers;
use App\Models\ChangYong;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChangYongResource extends Resource
{
    protected static ?string $model = ChangYong::class;

    protected static ?string $navigationLabel = "常用面額";
    protected static ?string $navigationGroup = "訂單";

    protected static ?string $modelLabel = "常用面額";
    protected static ?string $navigationIcon = 'heroicon-s-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('常用面額')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('price')
                    ->label('常用面額')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListChangYongs::route('/'),
            'create' => Pages\CreateChangYong::route('/create'),
            'edit' => Pages\EditChangYong::route('/{record}/edit'),
        ];
    }
}
