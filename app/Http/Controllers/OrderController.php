<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Orders;
use App\Service\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController
{

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        $params = $request->input();
        return response()->json($this->orderService->store('onsite', $params));
    }

    public function getPreOrder($userId, $eventId)
    {
        //商品列表
        $items = $this->getItems($userId);

        //未完成預購單
        $pending = Orders::select('id','preorder_name', 'order_amount', 'item_quantities')
                ->where('owner_id', $userId)
                ->where('event_id', $eventId)
                ->where('order_type', 'preorder')
                ->where('status','nonpayed')
                ->get()->toArray();
        foreach ($pending as &$item)
        {
            foreach($item['item_quantities'] as $key => $row)
            {
                $item['item_quantities'][$key]['item_name'] = $items[$row['item_id']];
            }
        }

        //已完成預購單
        $complete = Orders::select('id','trade_no','preorder_name', 'order_amount', 'item_quantities', 'updated_at')
            ->where('owner_id', $userId)
            ->where('event_id', $eventId)
            ->where('order_type', 'preorder')
            ->where('status','payed')
            ->get()->toArray();
        foreach ($complete as &$item)
        {
            foreach($item['item_quantities'] as $key => $row)
            {
                $item['item_quantities'][$key]['item_name'] = $items[$row['item_id']];
            }
        }


        return response()->json([
            'pendingReservations' => $pending,
            'completedReservations' => $complete
        ]);
    }

    public function savePreOrder(Request $request)
    {
        $params = $request->input();
        return response()->json($this->orderService->store('preorder', $params));
    }

    public function checkPreOrder($orderID, Request $request)
    {
        $items = $this->getItems($request->input('user_id'));
        try {
            DB::beginTransaction();
            //order
            $order = Orders::find($orderID);
            $order->status = 'payed';
            $order->save();

            $itemList = $order->item_quantities;
            foreach($itemList as &$item)
            {
                $item['item_name'] = $items[$item['item_id']];
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'reservation' => [
                    'id' => $order->id,
                    'trade_no'=> $order->trade_no,
                    'preorder_name' => $order->preorder_name,
                    'order_amount' => $order->order_amount,
                    'item_quantities' => $itemList,
                    'updated_at' => $order->updated_at,
                ]
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '處理預留單時發生錯誤: ' . $e->getMessage()
            ], 500);
        }

    }

    public function rollbackPreOrder($orderID, Request $request)
    {
        $items = $this->getItems($request->input('user_id'));
        try{
            $order = Orders::find($orderID);
            $order->status = 'nonpayed';
            $order->save();

            $itemList = $order->item_quantities;
            foreach($itemList as &$item)
            {
                $item['item_name'] = $items[$item['item_id']];
            }

            return response()->json([
                'success' => true,
                'reservation' => [
                    'id' => $order->id,
                    'order_amount' => $order->order_amount,
                    'item_quantities' => $itemList,
                    'preorder_name' => $order->preorder_name,
                ]
            ]);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '回退預留單時發生錯誤: ' . $e->getMessage()
            ], 500);
        }

    }

    public function delPreOrder($orderID)
    {
        try{
            DB::beginTransaction();
            $order = Orders::find($orderID);
            $itemList = $order->item_quantities;
            foreach($itemList as &$item)
            {
               $sellItems = Items::find($item['item_id']);
               $sellItems->item_stock += $item['quantity'];
               $sellItems->save();
            }
            $order->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'reservation' => [
                    'id' => $order->id,
                ]
            ]);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '刪除預留單時發生錯誤: ' . $e->getMessage()
            ], 500);
        }

    }

    private function getItems($userId)
    {
        return Items::where('owner_id', $userId)
            ->get()
            ->pluck('item_name', 'id')
            ->toArray();
    }
}
