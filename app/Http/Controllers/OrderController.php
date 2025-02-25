<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController
{

    public function store(Request $request)
    {
        $params = $request->input();
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

                $item = Items::find($row['id']);
                $item->item_stock -= $row['quantity'];
                $item->save();
            }

            //新增訂單
            $order = new Orders();
            $order->owner_id = $params['ownerId'];
            $order->event_id = $params['sessionId'];
            $order->trade_no = $params['id'];
            $order->order_type = 'onsite';
            $order->status = 'payed';
            $order->payment_id = $params['paymentMethod']['id'];
            $order->order_amount= $params['total'];
            $order->item_lists = $item_list;
            $order->item_quantities = $item_quantities;

            $order->save();
            DB::commit();
            return ['status'=> 'S'];
        }
        catch (\Exception $exception){
            DB::rollBack();
            dd($exception->getMessage());
            return ['status'=> 'F', 'msg' => $exception->getMessage()];
        }
    }
}
