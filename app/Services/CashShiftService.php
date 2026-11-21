<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\StoreBranch;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CashShiftService
{
    public function __construct(
        protected BcvExchangeService $exchangeService
    ) {}

    /**
     * Obtener o inicializar la caja registradora por defecto para una sucursal
     */
    public function getOrCreateDefaultRegister(StoreBranch $branch): CashRegister
    {
        return CashRegister::firstOrCreate(
            [
                'store_branch_id' => $branch->id,
                'name' => 'Caja Principal - '.$branch->branch_name,
            ],
            [
                'status' => 'active',
            ]
        );
    }

    /**
     * Abre un nuevo turno de caja registradora
     */
    public function openShift(
        CashRegister $register,
        User $cashier,
        float $openingBalanceUsd = 0.00,
        float $openingBalanceVes = 0.00,
        ?string $notes = null
    ): CashShift {
        // Verificar si la caja ya tiene un turno abierto
        $existingOpenShift = CashShift::where('cash_register_id', $register->id)
            ->where('status', 'open')
            ->first();

        if ($existingOpenShift) {
            throw ValidationException::withMessages([
                'shift' => "La caja '{$register->name}' ya tiene un turno abierto por {$existingOpenShift->cashier->full_name}.",
            ]);
        }

        return CashShift::create([
            'cash_register_id' => $register->id,
            'user_id' => $cashier->id,
            'opened_at' => now(),
            'opening_balance_usd' => round(max(0, $openingBalanceUsd), 2),
            'opening_balance_ves' => round(max(0, $openingBalanceVes), 2),
            'expected_cash_usd' => round(max(0, $openingBalanceUsd), 2),
            'expected_cash_ves' => round(max(0, $openingBalanceVes), 2),
            'status' => 'open',
            'notes' => $notes,
        ]);
    }

    /**
     * Cierra el turno de caja (Corte Z) y calcula sobrantes o faltantes
     */
    public function closeShift(
        CashShift $shift,
        float $actualCashUsd,
        float $actualCashVes,
        ?string $closingNotes = null
    ): CashShift {
        if ($shift->status !== 'open') {
            throw ValidationException::withMessages([
                'shift' => 'Este turno de caja ya se encuentra cerrado.',
            ]);
        }

        // Recomputar los montos esperados con las transacciones registradas
        $shift->recalculateExpectedCash();

        $expectedUsd = (float) $shift->expected_cash_usd;
        $expectedVes = (float) $shift->expected_cash_ves;

        $actualUsd = round(max(0, $actualCashUsd), 2);
        $actualVes = round(max(0, $actualCashVes), 2);

        $differenceUsd = round($actualUsd - $expectedUsd, 2);
        $differenceVes = round($actualVes - $expectedVes, 2);

        $shift->update([
            'closed_at' => now(),
            'actual_cash_usd' => $actualUsd,
            'actual_cash_ves' => $actualVes,
            'difference_usd' => $differenceUsd,
            'difference_ves' => $differenceVes,
            'status' => 'closed',
            'notes' => $closingNotes ? trim($shift->notes."\n[Cierre] ".$closingNotes) : $shift->notes,
        ]);

        return $shift->fresh();
    }

    /**
     * Genera el desglose completo de arqueo de caja (Corte X para turno abierto, Corte Z para turno cerrado)
     */
    public function getShiftSummary(CashShift $shift): array
    {
        $transactions = $shift->paymentTransactions()->with('order')->get();
        $store = $shift->register->branch->store;
        $rate = $this->exchangeService->getStoreRate($store);

        // Agrupar por método de pago
        $byMethod = [
            'efectivo_usd' => ['count' => 0, 'amount' => 0.00, 'usd_equivalent' => 0.00, 'currency' => 'USD', 'label' => '💵 Efectivo Divisas ($)'],
            'efectivo_ves' => ['count' => 0, 'amount' => 0.00, 'usd_equivalent' => 0.00, 'currency' => 'VES', 'label' => '💵 Efectivo Bolívares (Bs)'],
            'pago_movil' => ['count' => 0, 'amount' => 0.00, 'usd_equivalent' => 0.00, 'currency' => 'VES', 'label' => '📱 Pago Móvil (Bs)'],
            'punto_venta' => ['count' => 0, 'amount' => 0.00, 'usd_equivalent' => 0.00, 'currency' => 'VES', 'label' => '💳 Punto de Venta (Bs)'],
            'zelle' => ['count' => 0, 'amount' => 0.00, 'usd_equivalent' => 0.00, 'currency' => 'USD', 'label' => '⚡ Zelle ($)'],
            'jihova_wallet' => ['count' => 0, 'amount' => 0.00, 'usd_equivalent' => 0.00, 'currency' => 'USD', 'label' => '👛 Billetera JIHOVA ($)'],
        ];

        $totalSalesCount = $shift->orders()->count();
        $totalGrossUsd = 0.00;
        $totalIgtfUsd = 0.00;

        foreach ($transactions as $tx) {
            $m = $tx->method;
            if (! isset($byMethod[$m])) {
                $byMethod[$m] = [
                    'count' => 0,
                    'amount' => 0.00,
                    'usd_equivalent' => 0.00,
                    'currency' => $tx->currency,
                    'label' => $tx->methodLabel(),
                ];
            }

            $byMethod[$m]['count']++;
            $byMethod[$m]['amount'] = round($byMethod[$m]['amount'] + (float) $tx->amount, 2);
            $byMethod[$m]['usd_equivalent'] = round($byMethod[$m]['usd_equivalent'] + (float) $tx->amount_usd_equivalent, 2);

            $totalGrossUsd = round($totalGrossUsd + (float) $tx->amount_usd_equivalent, 2);
            $totalIgtfUsd = round($totalIgtfUsd + (float) $tx->igtf_amount, 2);
        }

        $expectedCashUsd = (float) $shift->opening_balance_usd + $byMethod['efectivo_usd']['amount'];
        $expectedCashVes = (float) $shift->opening_balance_ves + $byMethod['efectivo_ves']['amount'];

        return [
            'shift_id' => $shift->id,
            'status' => $shift->status,
            'type' => $shift->status === 'open' ? 'Corte X (Parcial en Vivo)' : 'Corte Z (Cierre Definitivo)',
            'register_name' => $shift->register->name,
            'cashier_name' => $shift->cashier->full_name,
            'opened_at' => $shift->opened_at->format('d/m/Y h:i A'),
            'closed_at' => $shift->closed_at ? $shift->closed_at->format('d/m/Y h:i A') : 'En curso',
            'exchange_rate' => $rate,
            'opening_balance_usd' => (float) $shift->opening_balance_usd,
            'opening_balance_ves' => (float) $shift->opening_balance_ves,
            'total_sales_count' => $totalSalesCount,
            'total_gross_usd' => $totalGrossUsd,
            'total_gross_ves' => $this->exchangeService->usdToVes($totalGrossUsd, $rate),
            'total_igtf_usd' => $totalIgtfUsd,
            'total_igtf_ves' => $this->exchangeService->usdToVes($totalIgtfUsd, $rate),
            'payments_by_method' => $byMethod,
            'expected_cash_usd' => round($expectedCashUsd, 2),
            'expected_cash_ves' => round($expectedCashVes, 2),
            'actual_cash_usd' => $shift->actual_cash_usd !== null ? (float) $shift->actual_cash_usd : null,
            'actual_cash_ves' => $shift->actual_cash_ves !== null ? (float) $shift->actual_cash_ves : null,
            'difference_usd' => $shift->difference_usd !== null ? (float) $shift->difference_usd : null,
            'difference_ves' => $shift->difference_ves !== null ? (float) $shift->difference_ves : null,
            'notes' => $shift->notes,
        ];
    }
}
