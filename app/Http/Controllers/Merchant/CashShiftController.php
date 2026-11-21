<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CashShift;
use App\Services\CashShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashShiftController extends Controller
{
    public function __construct(
        protected CashShiftService $shiftService
    ) {}

    /**
     * Abre un nuevo turno de caja con fondo inicial en USD y VES
     */
    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'register_id' => ['required', 'uuid', 'exists:cash_registers,id'],
            'opening_balance_usd' => ['required', 'numeric', 'min:0'],
            'opening_balance_ves' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $register = CashRegister::findOrFail($validated['register_id']);
        $cashier = $request->user();

        $shift = $this->shiftService->openShift(
            $register,
            $cashier,
            (float) $validated['opening_balance_usd'],
            (float) $validated['opening_balance_ves'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Turno de caja abierto satisfactoriamente.',
            'shift' => [
                'id' => $shift->id,
                'status' => $shift->status,
                'opened_at' => $shift->opened_at->format('d/m/Y h:i A'),
                'opening_balance_usd' => (float) $shift->opening_balance_usd,
                'opening_balance_ves' => (float) $shift->opening_balance_ves,
            ],
        ]);
    }

    /**
     * Cierra el turno de caja (Corte Z definitivo) con arqueo físico y arqueo de diferencias
     */
    public function close(Request $request, CashShift $shift): JsonResponse
    {
        $validated = $request->validate([
            'actual_cash_usd' => ['required', 'numeric', 'min:0'],
            'actual_cash_ves' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $closedShift = $this->shiftService->closeShift(
            $shift,
            (float) $validated['actual_cash_usd'],
            (float) $validated['actual_cash_ves'],
            $validated['closing_notes'] ?? null
        );

        $summary = $this->shiftService->getShiftSummary($closedShift);

        return response()->json([
            'success' => true,
            'message' => 'Turno de caja cerrado exitosamente (Corte Z).',
            'summary' => $summary,
        ]);
    }

    /**
     * Genera el arqueo parcial en tiempo real (Corte X) sin cerrar la caja
     */
    public function corteX(CashShift $shift): JsonResponse
    {
        $summary = $this->shiftService->getShiftSummary($shift);

        return response()->json([
            'success' => true,
            'type' => 'Corte X (Parcial)',
            'summary' => $summary,
        ]);
    }

    /**
     * Consulta el resumen final de cierre (Corte Z)
     */
    public function corteZ(CashShift $shift): JsonResponse
    {
        $summary = $this->shiftService->getShiftSummary($shift);

        return response()->json([
            'success' => true,
            'type' => 'Corte Z (Cierre definitivo)',
            'summary' => $summary,
        ]);
    }
}
