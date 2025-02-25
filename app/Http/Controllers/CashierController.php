<?php

namespace App\Http\Controllers;

use App\Models\ChangYong;
use App\Models\Items;
use App\Models\Payment;
use Inertia\Inertia;

class CashierController
{
    public function index()
    {
        $id = auth()->user()->id;
        //取出商品
        $items = Items::where('owner_id', $id)->get()->toArray();
        //常用面額
        $quickAmounts = ChangYong::where('owner_id', $id)->get()->pluck('price')->toArray();
        //年紀判別
        $date = now()->subYears(18);
        $r18Date = ($date->year - 1911).'/'.$date->month.'/'.$date->day;
        //支付方式
        $payment = Payment::where('owner_id', $id)->get()->toArray();

        return Inertia::render('Cashier', [
                'items' => $items,
                'quickAmounts' => $quickAmounts,
                'r18Date' => $r18Date,
                'payment' => $payment,
            ]
        );
    }
    public function show()
    {
        return Inertia::render('Show');
    }
}
