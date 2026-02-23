<?php

namespace Paymenter\Extensions\Others\CurrencyUpdater;

use App\Attributes\ExtensionMeta;
use App\Classes\Extension\Extension;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;

#[ExtensionMeta(
    name: 'CurrencyUpdater',
    description: 'Fetch FX Rates Automatically.',
    version: '1.4',
    author: 'DigiDome'
)]
class CurrencyUpdater extends Extension
{
    public function installed(): void
    {
        // no-op
    }

    public function enabled(): void
    {
        Artisan::call('migrate', [
            '--path'  => 'extensions/Others/CurrencyUpdater/database/migrations',
            '--force' => true,
        ]);
    }

    public function disabled(): void
    {
        // keep audit/log tables
    }

    public function boot(): void
    {
        View::addNamespace('currency-updater', __DIR__ . '/resources/views');

        // Pages in Admin/Pages are auto-discovered by Paymenter's AdminPanelProvider.
        // No manual Filament panel registration needed.

        try {
            Artisan::starting(function ($artisan) {
                $artisan->resolveCommands([
                    \Paymenter\Extensions\Others\CurrencyUpdater\src\Console\UpdateCurrencyRates::class,
                ]);
            });
        } catch (\Throwable $e) {
            // Silently ignore if Artisan is not ready
        }
    }

    public function getConfig($values = []): array
    {
        return [];
    }
}
