<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use Illuminate\Http\RedirectResponse;

class CurrencyController extends Controller
{
    public function __construct(protected ExchangeRateService $rateService) {}

    /**
     * Manually trigger a live rate sync and redirect back to dashboard.
     */
    public function updateRates(): RedirectResponse
    {
        $result = $this->rateService->syncRates(forceRefresh: true);

        if ($result['success']) {
            return redirect()->route('dashboard')
                ->with('success', $result['message']);
        }

        return redirect()->route('dashboard')
            ->with('error', $result['message']);
    }
}
