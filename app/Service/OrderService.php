<?php

namespace App\Service;

use App\Models\Events;
use App\Models\Items;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(Orders $orders)
    {
        $this->orders = $orders;
    }

    public function store($type, $params)
    {
        DB::beginTransaction();
        try{

            $item_list = [];
            $item_quantities = [];
            foreach($params['items'] as $row)
            {
                $item_list[] = $row['id'];
                $item_quantities[] = [
                    'item_id' => $row['id'],
                    'quantity' => $row['quantity'],
                ];

                //修改庫存
                $item = Items::find($row['id']);
                $item->item_stock -= $row['quantity'];
                $item->save();
            }

            $event = Events::find($params['sessionId']);

            //新增訂單
            $order = new Orders();
            $order->owner_id = $params['ownerId'];
            $order->event_id = $params['sessionId'];
            $order->trade_no = $params['id'];
            $order->order_type = $type;
            $order->status = ($type === 'preorder') ? 'nonpayed' : 'payed';
            $order->payment_id = $params['paymentMethod']['id'] ?? 1;
            $order->order_amount= $params['total'];
            $order->item_lists = $item_list;
            $order->item_quantities = $item_quantities;
            $order->preorder_name = $event->event_name.' 現場客人_'.time();

            $order->save();
            DB::commit();
            return ['status'=> 200];
        }
        catch (\Exception $exception){
            DB::rollBack();
            dd($exception->getMessage());
            return ['status'=> 'F', 'msg' => $exception->getMessage()];
        }
    }
}
