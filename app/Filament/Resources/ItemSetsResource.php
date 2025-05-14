<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemSetsResource\Pages;
use App\Filament\Resources\ItemSetsResource\RelationManagers;
use App\Models\Events;
use App\Models\Items;
use App\Models\ItemSets;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    public static function canViewAny(): bool
    {
        return auth()->user()->user_role === 'admin';
    }

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
        $id = auth()->user()->id;

        //場次
        $events = Events::where('owner_id', $id)->get()->pluck('event_name','id')->toArray();
        $events[0] = '全場次';
        ksort($events);

        //商品
        $items = Items::where('owner_id', $id)->get()->pluck('item_name','id')->toArray();

        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('event_id')
                        ->label('場次')
                        ->required()
                        ->options($events),
                    Forms\Components\TextInput::make('item_set_name')
                        ->label('商品組合名稱')
                        ->required(),
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
                    Forms\Components\CheckboxList::make('item_set_list')
                        ->label('可組合商品選項')
                        ->options($items)
                        ->columns(3)
                        ->required()
                        ->helperText('請選擇販賣商品')
                        ->reactive()
                        ->afterStateHydrated(function (Set $set, Get $get, $state) use ($items) {
                            // 在表單載入時處理已存在的資料
                            $itemQuantities = $get('item_quantities') ?? [];
                            // 如果 item_quantities 已有資料但缺少項目名稱，則補充
                            if (!empty($itemQuantities)) {
                                $updatedQuantities = collect($itemQuantities)->map(function ($item) use ($items) {
                                    // 確保 item_name 存在，如果不存在則從 $items 中獲取
                                    if (!isset($item['item_name']) && isset($item['item_id'])) {
                                        $item['item_name'] = $items[$item['item_id']] ?? '商品 #' . $item['item_id'];
                                    }
                                    return $item;
                                })->toArray();

                                $set('item_quantities', $updatedQuantities);

                                // 同時設置 item_lists 為已選商品 ID 的陣列
                                $itemIds = collect($updatedQuantities)->pluck('item_id')->toArray();
                                $set('item_set_list', $itemIds);
                            }
                        })
                        ->afterStateUpdated(function (Set $set, Get $get, $state) use ($items) {
                            // 當選擇變更時處理
                            $currentQuantities = $get('item_quantities') ?? [];

                            // 為新選擇的商品創建項目
                            $quantities = [];
                            foreach ($state as $itemId) {
                                // 檢查該項目是否已存在
                                $exists = false;
                                if (is_array($currentQuantities)) {
                                    foreach ($currentQuantities as $index => $item) {
                                        if (isset($item['item_id']) && $item['item_id'] == $itemId) {
                                            $exists = true;
                                            $quantities[] = $item;
                                            break;
                                        }
                                    }
                                }

                                // 如果不存在，則新增項目
                                if (!$exists) {
                                    $quantities[] = [
                                        'item_id' => $itemId,
                                        'item_name' => $items[$itemId] ?? '商品 #' . $itemId,
                                        'quantity' => 1
                                    ];
                                }
                            }

                            // 更新 item_quantities 欄位
                            $set('item_quantities', $quantities);
                        }),
                    Forms\Components\Section::make('商品數量設定')
                        ->schema([
                            Forms\Components\Repeater::make('item_quantities')
                                ->label(false)
                                ->schema([
                                    Forms\Components\Hidden::make('item_id'),
                                    Forms\Components\Grid::make()
                                        ->schema([
                                            Forms\Components\TextInput::make('item_name')
                                                ->label('商品名稱')
                                                ->disabled(),
                                            Forms\Components\TextInput::make('quantity')
                                                ->label('數量')
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(1)
                                                ->required(),
                                        ]),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['item_name'] ?? null)
                                ->columns(1)
                                ->visible(fn (Get $get): bool => !empty($get('item_set_list')))
                                ->addable(false)
                                ->deletable(false),
                        ])
                        ->visible(fn (Get $get): bool => !empty($get('item_set_list'))),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
