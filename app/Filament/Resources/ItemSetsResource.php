<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemSetsResource\Pages;
use App\Filament\Resources\ItemSetsResource\RelationManagers;
use App\Models\ItemSets;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemSetsResource extends Resource
{
    protected static ?string $model = ItemSets::class;
    protected static ?string $navigationLabel = "商品組合";
    protected static ?string $navigationGroup = "商品";
    protected static ?string $modelLabel = "商品組合";
    protected static ?string $navigationIcon = 'heroicon-s-square-3-stack-3d';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('item_set_name')
                        ->label('商品組合名稱')
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('item_set_name_en')
                        ->label('商品組合名稱（英文）')
                        ->default(null),
                    Forms\Components\TextInput::make('item_set_name_jp')
                        ->label('商品組合名稱（日文）')
                        ->default(null),
                    Forms\Components\TextInput::make('item_set_price')
                        ->label('商品組合價格')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\TextInput::make('item_set_stock')
                        ->label('商品組合庫存')
                        ->required()
                        ->numeric()
                        ->default(0),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_set_name')
                    ->label('商品組合名稱')
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_set_name_en')
                    ->label('商品組合名稱（英文）')
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_set_name_jp')
                    ->label('商品組合名稱（日文）')
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_set_price')
                    ->label('商品組合價格')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_set_stock')
                    ->label('商品組合庫存')
                    ->numeric()
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
            'index' => Pages\ListItemSets::route('/'),
            'create' => Pages\CreateItemSets::route('/create'),
            'edit' => Pages\EditItemSets::route('/{record}/edit'),
        ];
    }
}
