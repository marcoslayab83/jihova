<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SubOrder;
use App\Services\BcvExchangeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThermalPrintController extends Controller
{
    public function __construct(
        protected BcvExchangeService $exchangeService
    ) {}

    /**
     * Muestra la vista de impresión de recibo térmico (80mm o 58mm) para una orden
     */
    public function receipt(Order $order, Request $request): View
    {
        $order->load([
            'subOrders.branch.store',
            'subOrders.items.product',
            'paymentTransactions',
            'cashShift.cashier',
            'cashShift.register',
        ]);

        $subOrder = $order->subOrders->first();
        $branch = $subOrder?->branch;
        $store = $branch?->store;

        $format = $request->query('format', $branch?->invoice_printer_type ?? '80mm');
        $rate = (float) ($order->bcv_exchange_rate ?? ($store ? $this->exchangeService->getStoreRate($store) : 50.00));

        return view('portals.merchant.pos.receipt-thermal', [
            'order' => $order,
            'subOrder' => $subOrder,
            'branch' => $branch,
            'store' => $store,
            'cashShift' => $order->cashShift,
            'cashier' => $order->cashShift?->cashier,
            'items' => $subOrder?->items ?? collect(),
            'payments' => $order->paymentTransactions,
            'format' => $format,
            'rate' => $rate,
            'exchangeService' => $this->exchangeService,
        ]);
    }

    /**
     * Muestra la comanda térmica de cocina / despacho para pedidos online
     */
    public function orderComanda(SubOrder $subOrder, Request $request): View
    {
        $subOrder->load([
            'order.customer',
            'order.address',
            'branch.store',
            'items.product',
        ]);

        $format = $request->query('format', $subOrder->branch->invoice_printer_type ?? '80mm');

        return view('portals.merchant.pos.comanda-thermal', [
            'subOrder' => $subOrder,
            'order' => $subOrder->order,
            'branch' => $subOrder->branch,
            'store' => $subOrder->branch->store,
            'items' => $subOrder->items,
            'format' => $format,
        ]);
    }
}
