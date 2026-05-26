<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Base API URL - uses USD as the base currency.
     * Free tier from open.er-api.com, no API key required.
     */
    protected string $apiUrl = 'https://open.er-api.com/v6/latest/USD';

    /**
     * Cache key for storing the raw API response.
     */
    protected string $cacheKey = 'exchange_rates_usd_base';

    /**
     * How many seconds to cache the rates (6 hours to avoid hammering the free tier).
     */
    protected int $cacheTtl = 60 * 60 * 6;

    /**
     * Fetch live rates and sync them to the currencies table.
     * Returns an array with 'success', 'message', and optionally 'updated' count.
     */
    public function syncRates(bool $forceRefresh = false): array
    {
        try {
            $data = $this->fetchRates($forceRefresh);

            if (! $data || ! isset($data['rates'])) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data kurs dari server. Respons tidak valid.',
                ];
            }

            $rates   = $data['rates'];   // Array: ['IDR' => 16400, 'TWD' => 32.5, ...]
            $updated = 0;

            // USD is the base (rate = 1.0 relative to USD)
            // Our exchange_rate_to_usd stores: how many USD does 1 unit of this currency equal?
            // So: exchange_rate_to_usd = 1 / rates[currency_code]
            Currency::all()->each(function (Currency $currency) use ($rates, &$updated) {
                $code = strtoupper($currency->code);

                if ($code === 'USD') {
                    // USD base: always 1.0
                    $rateToUsd = 1.0;
                } elseif (isset($rates[$code])) {
                    // rate from API = how many units of $code per 1 USD
                    // → exchange_rate_to_usd = 1 / rate
                    $rateToUsd = 1.0 / (float) $rates[$code];
                } else {
                    // Currency not in API response, skip
                    return;
                }

                $currency->update(['exchange_rate_to_usd' => $rateToUsd]);
                $updated++;
            });

            $timestamp = $data['time_last_update_utc'] ?? now()->toDateTimeString();

            return [
                'success'   => true,
                'updated'   => $updated,
                'timestamp' => $timestamp,
                'message'   => "Berhasil memperbarui kurs {$updated} mata uang. Data per: {$timestamp}",
            ];
        } catch (\Exception $e) {
            Log::error('ExchangeRateService syncRates error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke server kurs. Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Auto-sync rates once per day using Laravel Cache.
     * Called from DashboardController so users always have fresh rates
     * without manual intervention.
     */
    public function autoSyncIfStale(): void
    {
        $dailyKey = 'exchange_rates_synced_today';

        if (! Cache::has($dailyKey)) {
            $result = $this->syncRates(forceRefresh: true);

            if ($result['success']) {
                // Mark as synced for the next 23 hours
                Cache::put($dailyKey, now()->toDateTimeString(), now()->addHours(23));
                Log::info('Exchange rates auto-synced: ' . $result['message']);
            }
        }
    }

    /**
     * Fetch data from API (cached).
     */
    protected function fetchRates(bool $forceRefresh = false): ?array
    {
        if ($forceRefresh) {
            Cache::forget($this->cacheKey);
        }

        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            $response = Http::timeout(10)->get($this->apiUrl);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }

    /**
     * Return last cached rates data for display (no DB sync).
     */
    public function getCachedRates(): ?array
    {
        return Cache::get($this->cacheKey);
    }
}
