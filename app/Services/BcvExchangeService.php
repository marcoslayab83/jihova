<?php

namespace App\Services;

use App\Models\Store;

class BcvExchangeService
{
    public const DEFAULT_BCV_RATE = 50.0000;
    public const IGTF_PERCENT = 3.00;

    /**
     * Obtener la tasa de cambio vigente para el comercio (configurable por tienda o tasa base)
     */
    public function getStoreRate(?Store $store): float
    {
        if ($store && $store->bcv_rate && (float) $store->bcv_rate > 0) {
            return (float) $store->bcv_rate;
        }

        return self::DEFAULT_BCV_RATE;
    }

    /**
     * Convertir USD a Bolívares (VES)
     */
    public function usdToVes(float $amountUsd, float $rate): float
    {
        return round($amountUsd * $rate, 2);
    }

    /**
     * Convertir Bolívares (VES) a USD
     */
    public function vesToUsd(float $amountVes, float $rate): float
    {
        if ($rate <= 0) {
            return 0.00;
        }

        return round($amountVes / $rate, 2);
    }

    /**
     * Calcula el 3% de IGTF aplicable ÚNICAMENTE sobre las fracciones pagadas en divisas
     * (Efectivo USD o Zelle), siempre y cuando el comercio sea Contribuyente Especial.
     *
     * @param  list<array{method: string, currency: string, amount: float}>  $payments
     */
    public function calculateIgtf(array $payments, bool $isSpecialTaxpayer, float $rate): array
    {
        if (! $isSpecialTaxpayer) {
            return [
                'applicable' => false,
                'foreign_currency_usd' => 0.00,
                'igtf_usd' => 0.00,
                'igtf_ves' => 0.00,
            ];
        }

        $foreignCurrencyUsd = 0.00;

        foreach ($payments as $payment) {
            $method = strtolower($payment['method'] ?? '');
            $currency = strtoupper($payment['currency'] ?? 'USD');
            $amount = (float) ($payment['amount'] ?? 0);

            // Métodos en moneda extranjera sujetos a IGTF
            if (in_array($method, ['efectivo_usd', 'zelle'], true) || ($currency === 'USD' && ! in_array($method, ['jihova_wallet'], true))) {
                $foreignCurrencyUsd += $amount;
            }
        }

        $igtfUsd = round($foreignCurrencyUsd * (self::IGTF_PERCENT / 100), 2);
        $igtfVes = $this->usdToVes($igtfUsd, $rate);

        return [
            'applicable' => true,
            'foreign_currency_usd' => round($foreignCurrencyUsd, 2),
            'igtf_usd' => $igtfUsd,
            'igtf_ves' => $igtfVes,
        ];
    }

    /**
     * Calcula el vuelto en divisas y bolívares según el monto pagado y total a pagar
     */
    public function calculateChange(float $totalDueUsd, float $totalPaidUsd, float $rate): array
    {
        $changeUsd = round(max(0.00, $totalPaidUsd - $totalDueUsd), 2);
        $changeVes = $this->usdToVes($changeUsd, $rate);

        return [
            'has_change' => $changeUsd > 0.00,
            'change_usd' => $changeUsd,
            'change_ves' => $changeVes,
        ];
    }
}
