<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Items;
use App\Models\Orders;

class ItemsController extends Controller
{
    public function getItems($userId, $eventId)
    {
        //場次id, 包含全場次
        $eventId=[0, $eventId];

        //撈出預訂且尚未付款的訂單並處理商品數量
        $itemQuantities = [];

// 直接在查詢中只選取需要的欄位
        $orders = Orders::select('item_quantities')
            ->where('owner_id', $userId)
            ->where('order_type', 'preorder')
            ->where('status','nonpayed')
            ->whereIn('event_id', $eventId)
            ->get();

// 使用集合方法整合所有訂單中相同商品的數量
        $orders->each(function ($order) use (&$itemQuantities) {
            collect($order->item_quantities)->each(function ($item) use (&$itemQuantities) {
                $itemId = (string) $item['item_id']; // 統一轉為字串類型
                $itemQuantities[$itemId] = ($itemQuantities[$itemId] ?? 0) + $item['quantity'];
            });
        });

// 撈出所有商品並使用 with 方法預先載入可能需要的關聯
        $items = Items::where('owner_id', $userId)
            ->whereIn('event_id', $eventId)
            ->get()
            ->map(function ($item) use ($itemQuantities) {
                $itemId = (string) $item->id;
                $item->preOrder = 0;
                // 如果該商品在預訂清單中
                if (isset($itemQuantities[$itemId])) {
                    // 記錄已預訂數量以便前端顯示
                    $item->preOrder = $itemQuantities[$itemId];
                }
                return $item;
            });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'total_items' => $items->count(),
                'preordered_items_count' => count($itemQuantities)
            ]
        ]);
    }
}
