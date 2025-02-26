<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemTypeResource\Pages;
use App\Filament\Resources\ItemTypeResource\RelationManagers;
use App\Models\ItemType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemTypeResource extends Resource
{
    protected static ?string $model = ItemType::class;

    protected static ?string $navigationLabel = "商品類別";
    protected static ?string $navigationGroup = "商品";
    protected static ?string $modelLabel = "商品類別";
    protected static ?string $navigationIcon = 'heroicon-m-funnel';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if(auth()->user()->user_role !== 'admin')
        {
            $query->where('owner_id', auth()->user()->id);
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('item_type_name')
                        ->label('商品類別名稱')
                        ->required()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('item_type_name_en')
                        ->label('商品類別名稱（英文）')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('item_type_name_jp')
                        ->label('商品類別名稱（日文）')
                        ->maxLength(50),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_type_name')
                    ->label('商品類別名稱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_type_name_en')
                    ->label('商品類別名稱（英文）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item_type_name_jp')
                    ->label('商品類別名稱（日文）')
                    ->searchable(),
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
            'index' => Pages\ListItemTypes::route('/'),
            'create' => Pages\CreateItemType::route('/create'),
            'edit' => Pages\EditItemType::route('/{record}/edit'),
        ];
    }
}
