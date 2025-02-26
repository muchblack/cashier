<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemsResource\Pages;
use App\Filament\Resources\ItemsResource\RelationManagers;
use App\Models\Events;
use App\Models\Items;
use App\Models\ItemType;
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if(!auth()->user()->user_role === 'admin')
        {
            $query->where('owner_id', auth()->user()->id);
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        $id = auth()->user()->id;

        //場次
        $events = Events::where('owner_id', $id)->get()->pluck('event_name','id')->toArray();
        $events[0] = '全場次';
        ksort($events);

        //商品類別
        $itemTypes = ItemType::where('owner_id', $id)->get()->pluck('item_type_name','id')->toArray();
        $itemTypes[0]='無類別';
        ksort($itemTypes);

        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('event_id')
                        ->label('場次')
                        ->required()
                        ->options($events),
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
                        ->options($itemTypes),
                    Forms\Components\TextInput::make('item_price')
                        ->label('商品價格')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\TextInput::make('item_stock')
                        ->label('商品總庫存')
                        ->required()
                        ->numeric()
                        ->helperText('請輸入此商品總庫存，數字會根據預購單和商品組合數量而定')
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
                Tables\Columns\TextColumn::make('event.event_name')
                    ->label('販售場次')
                    ->state(function ($record) {

                        if($record->event_id === 0 )
                        {
                            return "全場次販售";
                        }
                        else
                        {
                            return $record->event->event_name;
                        }
                    }),
                Tables\Columns\TextColumn::make('item_name_en')
                    ->label('商品名稱（英文）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_name_jp')
                    ->label('商品名稱（日文）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('itemType.item_type_name')
                    ->label('商品類別')
                    ->state(function ($record) {
                        if($record->item_type_id === 0 )
                        {
                            return "無類別";
                        }
                        else
                        {
                            return $record->itemType->item_type_name;
                        }
                    }),
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
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'success',
                        '1' => 'danger'
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => '全年齡',
                        '1' => '18+',
                    }),
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
