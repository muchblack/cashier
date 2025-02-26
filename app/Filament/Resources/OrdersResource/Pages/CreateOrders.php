<?php

namespace App\Filament\Resources\OrdersResource\Pages;

use App\Filament\Resources\OrdersResource;
use App\Models\Items;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrders extends CreateRecord
{
    protected static string $resource = OrdersResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->id();
        $order_amount = 0;
        $item_quantities = $data['item_quantities'];
        foreach($item_quantities as $item){
            $ownItem = Items::find($item['item_id']);
            $order_amount += $ownItem->item_price * $item['quantity'];
        }
        $data['order_amount'] = $order_amount;
        return $data;
    }

    protected function afterCreate()
    {
        $item_list = $this->record->item_quantities;
        foreach ($item_list as $item) {
            $sellItem = Items::find($item['item_id']);
            $sellItem->item_stock -= $item['quantity'];
            $sellItem->save();
        }
    }
}
