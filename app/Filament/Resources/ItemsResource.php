<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemsResource\Pages;
use App\Filament\Resources\ItemsResource\RelationManagers;
use App\Models\Items;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsResource extends Resource
{
    protected static ?string $model = Items::class;

    protected static ?string $navigationLabel = "商品列表";
    protected static ?string $navigationGroup = "商品";
    protected static ?string $modelLabel = "商品列表";
    protected static ?string $navigationIcon = 'heroicon-c-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('event_id')
                        ->label('場次')
                        ->options([
                            0 => '全場次'
                        ]),
                    Forms\Components\TextInput::make('item_name')
                        ->label('商品名稱')
                        ->required()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('item_name_en')
                        ->label('商品名稱（英文）')
                        ->maxLength(50)
                        ->default(null),
                    Forms\Components\TextInput::make('item_name_jp')
                        ->label('商品名稱（日文）')
                        ->maxLength(50)
                        ->default(null),
                    Forms\Components\Select::make('item_type_id')
                        ->label('商品種類')
                        ->required()
                        ->options([
                            '0' => '無類別',
                        ]),
                    Forms\Components\TextInput::make('item_price')
                        ->label('商品價格')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\TextInput::make('item_stock')
                        ->label('商品庫存')
                        ->required()
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_r18')
                        ->label('是否爲R18商品')
                        ->required()
                        ->default(0),
                    Forms\Components\FileUpload::make('item_img_url')
                        ->label('商品圖片'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('商品名稱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_name_en')
                    ->label('商品名稱（英文）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_name_jp')
                    ->label('商品名稱（日文）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_type_id')
                    ->label('商品類別')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_price')
                    ->label('商品價格')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_stock')
                    ->label('商品庫存')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_r18')
                    ->label('R18商品')
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItems::route('/create'),
            'edit' => Pages\EditItems::route('/{record}/edit'),
        ];
    }
}
