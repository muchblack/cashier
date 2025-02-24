<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdersResource\Pages;
use App\Filament\Resources\OrdersResource\RelationManagers;
use App\Models\Orders;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersResource extends Resource
{
    protected static ?string $model = Orders::class;
    protected static ?string $navigationLabel = "訂單";
    protected static ?string $navigationGroup = "訂單";
    protected static ?string $modelLabel = "訂單";
    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('item_id')
                        ->label('商品Id')
                        ->required()
                        ->numeric(),
                    Forms\Components\Select::make('event_id')
                        ->label('場次')
                        ->options([
                            0 => '全場次'
                        ]),
                    Forms\Components\Select::make('order_type')
                        ->label('訂單類別')
                        ->options([
                            'preorder' => '預訂單',
                            'onsite' => '現場單'
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('訂單付款狀態')
                        ->options([
                            'payed' => '已付款',
                            'nonpayed' => '未付款'
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('order_amount')
                        ->label('訂單金額')
                        ->required()
                        ->numeric(),
                    Forms\Components\Textarea::make('item_lists')
                        ->label('訂單內容')
                        ->required()
                        ->columnSpanFull(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('order_amount')
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrders::route('/create'),
            'edit' => Pages\EditOrders::route('/{record}/edit'),
        ];
    }
}
