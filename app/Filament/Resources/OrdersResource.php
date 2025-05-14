<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdersResource\Pages;
use App\Filament\Resources\OrdersResource\RelationManagers;
use App\Models\Events;
use App\Models\Items;
use App\Models\Orders;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class OrdersResource extends Resource
{
    protected static ?string $model = Orders::class;
    protected static ?string $navigationLabel = "訂單";
    protected static ?string $navigationGroup = "訂單";
    protected static ?string $modelLabel = "訂單";
    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if(auth()->user()->user_role !== 'admin')
        {
            $query->where('owner_id', auth()->id());
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        $id = auth()->user()->id;
        $payment = Payment::where('owner_id', $id)->get()->pluck('payment_name','id')->toArray();

        //商品
        $items = Items::where('owner_id', $id)->get()->pluck('item_name','id')->toArray();

        //場次
        $events = Events::where('owner_id', $id)->get()->pluck('event_name','id')->toArray();

        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('event_id')
                        ->label('場次')
                        ->options($events),
                    Forms\Components\Select::make('order_type')
                        ->label('訂單類別')
                        ->options([
                            'preorder' => '預訂單',
                            'onsite' => '現場單'
                        ])
                        ->required()
                        ->reactive(),
                    Forms\Components\Select::make('payment_id')
                        ->label('付款方式')
                        ->required()
                        ->options($payment),
                    Forms\Components\TextInput::make('preorder_name')
                        ->label('預訂者名稱/暱稱')
                        ->visible(function(Get $get){
                            if($get('order_type') === 'preorder')
                            {
                                return true;
                            }
                            return false;
                        }),
                    Forms\Components\TextInput::make('plurk_account')
                        ->label('預訂者噗浪帳號')
                        ->visible(function(Get $get){
                            if($get('order_type') === 'preorder')
                            {
                                return true;
                            }
                            return false;
                        }),
                    Forms\Components\Select::make('status')
                        ->label('訂單付款狀態')
                        ->options([
                            'payed' => '已付款',
                            'nonpayed' => '未付款',
                            'preorder' => '已付定金'
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('order_amount')
                        ->label('訂單金額')
                        ->numeric(),
                    Forms\Components\TextInput::make('preorder_price')
                        ->label('已預付定金')
                        ->visible(function(Get $get){
                            if($get('order_type') === 'preorder')
                            {
                                return true;
                            }
                            return false;
                        }),
                    Forms\Components\CheckboxList::make('item_lists')
                        ->label('訂單商品')
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
                                $set('item_lists', $itemIds);
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
                                ->visible(fn (Get $get): bool => !empty($get('item_lists')))
                                ->addable(false)
                                ->deletable(false),
                        ])
                        ->visible(fn (Get $get): bool => !empty($get('item_lists'))),
                    Forms\Components\MarkdownEditor::make('order_desc')
                        ->label('訂單備註')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $events = Events::where('owner_id', auth()->id())->get()->pluck('event_name', 'id')->toArray();
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_type')
                    ->label('訂單類別')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'preorder' => 'success',
                        'onsite' => 'info'
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'preorder' => '預訂單',
                        'onsite' => '現場單',
                    }),
                Tables\Columns\TextColumn::make('preorder_name')
                    ->label('預訂者名稱'),
                Tables\Columns\TextColumn::make('status')
                    ->label('訂單狀況')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payed' => 'success',
                        'nonpayed' => 'danger',
                        'preorder' => 'info'
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'payed' => '已付款',
                        'nonpayed' => '未付款',
                        'preorder' => '已付定金'
                    }),
                Tables\Columns\TextColumn::make('order_amount')
                    ->label('訂單總金額')
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
                Tables\Filters\SelectFilter::make('order_type')
                    ->label('訂單種類')
                    ->options([
                        'preorder' => '預訂單',
                        'onsite' => '現場單',
                    ]),
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('場次')
                    ->options($events),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DateTimePicker::make('created_at_from')
                            ->label('建立時間(起始)')
                            ->placeholder('請選擇起始時間')
                            ->native(false),
                        Forms\Components\DateTimePicker::make('created_at_to')
                            ->label('建立時間（結束）')
                            ->placeholder('請選擇結束時間')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_at_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_at_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = '起始時間: ' . Carbon::parse($data['created_from'])->toDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = '結束時間: ' . Carbon::parse($data['created_until'])->toDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Model $record): bool => $record->order_type === 'preorder')
                    ->after(function (Model $record) {
                        DB::transaction(function () use ($record) {
                            // 1. 更新產品庫存
                            foreach ($record->item_quantities as $item) {
                                $sellItems = Items::find($item['item_id']);
                                $sellItems->item_stock += $item['quantity'];
                                $sellItems->save();
                            }
                        });
                    }),
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
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
