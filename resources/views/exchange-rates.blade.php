<x-filament-panels::page>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('refresh-rates', () => {
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            });
        });
    </script>

    <div class="space-y-6">
        <!-- Info Banner -->
        <div class="fi-banner">
            <div class="fi-banner-icon" aria-hidden="true">i</div>
            <div>
                <div class="fi-banner-title">Exchange Rates Management</div>
                <div class="fi-banner-desc">
                    Default (base) currency stays unchanged. Only Paymenter-enabled currencies are shown and updated.
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="fi-tabs">
            <div class="fi-tabs-list">
                <button
                    wire:click="switchTab('rates')"
                    class="fi-tab-btn {{ $activeTab === 'rates' ? 'fi-tab-active' : '' }}">
                    Exchange Rates
                </button>
                <button
                    wire:click="switchTab('config')"
                    class="fi-tab-btn {{ $activeTab === 'config' ? 'fi-tab-active' : '' }}">
                    API Configuration
                </button>
            </div>
        </div>

        <!-- Rates Tab -->
        @if ($activeTab === 'rates')
        <div class="fi-card">
            <div class="fi-toolbar">
                <strong class="fi-title">Manage Exchange Rates</strong>
                <div class="flex gap-2">
                    <button wire:click.prevent="fetchRates" class="fi-btn fi-btn-color-primary fi-btn-size-sm">
                        <span class="fi-btn-label">Fetch Latest Rates</span>
                    </button>
                    <button wire:click.prevent="saveRates" class="fi-btn fi-btn-color-success fi-btn-size-sm">
                        <span class="fi-btn-label">Save Changes</span>
                    </button>
                    <button wire:click.prevent="applyToProducts" class="fi-btn fi-btn-color-warning fi-btn-size-sm">
                        <span class="fi-btn-label">Apply to Products</span>
                    </button>
                </div>
            </div>

            <div class="fi-body">
                <div class="fi-table-wrap">
                    <table class="fi-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Rate (1 Base = x Code)</th>
                                <th>Enabled</th>
                                <th>Set Default</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rates = $this->getRates(); @endphp
                            @forelse ($rates as $rate)
                            <tr>
                                <td>
                                    <input type="text"
                                        value="{{ $rate->code }}"
                                        class="fi-input w-24"
                                        onblur="@this.updateRate({{ $rate->id }}, 'code', this.value.toUpperCase())">
                                </td>
                                <td>
                                    <input type="number" step="0.00000001" min="0"
                                        value="{{ number_format((float) $rate->rate, 8, '.', '') }}"
                                        class="fi-input w-40"
                                        onblur="@this.updateRate({{ $rate->id }}, 'rate', this.value)">
                                </td>
                                <td>
                                    <input type="checkbox"
                                        @if ($rate->enabled) checked @endif
                                    class="fi-checkbox"
                                    onchange="@this.updateRate({{ $rate->id }}, 'enabled', this.checked)">
                                </td>
                                <td>
                                    @if ($rate->is_default)
                                    <span class="fi-badge fi-badge-success">Default</span>
                                    @endif
                                    @if (! $rate->is_default)
                                    <button class="fi-btn fi-btn-color-secondary fi-btn-size-xs"
                                        wire:click="setDefault({{ $rate->id }})">
                                        Make Default
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center fi-empty">
                                    No currencies available. Check Paymenter currency settings.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="fi-tip">Tip: The default currency's rate is always 1.0 and remains unchanged.</p>
                <p class="fi-tip">Note: Only enabled currencies will be updated when using "Fetch Latest Rates". Disabled currencies will be skipped.</p>
            </div>
        </div>
        @endif

        <!-- API Config Tab -->
        @if ($activeTab === 'config')
        <div class="fi-card">
            <div class="fi-toolbar">
                <strong class="fi-title">API Configuration</strong>
                <div class="flex gap-2">
                    <button wire:click.prevent="saveApiConfig" class="fi-btn fi-btn-color-primary fi-btn-size-sm">
                        <span class="fi-btn-label">Save Configuration</span>
                    </button>
                    <button wire:click.prevent="fetchRates" class="fi-btn fi-btn-color-success fi-btn-size-sm">
                        <span class="fi-btn-label">Test API</span>
                    </button>
                </div>
            </div>
            <div class="fi-body">
                <div class="space-y-4">
                    <div>
                        <label class="fi-label">API URL</label>
                        <input type="url"
                            wire:model="apiUrl"
                            class="fi-input w-full"
                            placeholder="https://v6.exchangerate-api.com/v6/YOUR_API_KEY/latest/">
                        <p class="fi-hint">
                            Examples:<br>
                            • ExchangeRate-API: <code class="fi-code">https://v6.exchangerate-api.com/v6/&lt;KEY&gt;/latest/</code> (base auto-appended)<br>
                            • Frankfurter: <code class="fi-code">https://api.frankfurter.app/latest</code>
                        </p>
                    </div>

                    <div>
                        <label class="fi-label">Timeout (seconds)</label>
                        <input type="number"
                            wire:model="timeout"
                            min="1"
                            max="30"
                            class="fi-input w-32"
                            placeholder="8">
                        <p class="fi-hint">Request timeout in seconds (1–30).</p>
                    </div>
                </div>

                <div class="fi-info-box">
                    <h4 class="fi-info-title">Recommended API Providers</h4>
                    <div class="space-y-2 text-sm">
                        <div><strong>Frankfurter.app (Free, Recommended):</strong> https://api.frankfurter.app/latest</div>
                        <div><strong>ExchangeRate-API Base URL:</strong> https://v6.exchangerate-api.com/v6/YOUR_API_KEY/latest/</div>
                        <div class="fi-info-tip"><strong>Tip:</strong> Replace YOUR_API_KEY with your actual ExchangeRate-API key</div>
                        <div class="fi-info-note"><strong>Note:</strong> The system will automatically append your base currency to the URL</div>
                        <div class="fi-info-muted"><strong>Get API Key:</strong> Visit <a href="https://www.exchangerate-api.com/" target="_blank" class="fi-link">exchangerate-api.com</a> to get your free API key</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Footer with version info -->
    <div class="mt-6 text-center fi-footer-text">
        Currency Exchanger v1.4 - Automatic Exchange Rate Updates for Paymenter
    </div>

    <style>
        /* ── Banner ──────────────────────────────────────────── */
        .fi-banner {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            background: rgba(var(--gray-50, 248 250 252), 1);
            border: 1px solid rgba(var(--gray-200, 229 231 235), 1);
            border-radius: 12px;
        }
        .dark .fi-banner {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .fi-banner-title {
            font-weight: 600;
            color: rgba(var(--gray-950, 15 23 42), 1);
        }
        .dark .fi-banner-title {
            color: rgba(255, 255, 255, 0.95);
        }

        .fi-banner-desc {
            color: rgba(var(--gray-500, 71 85 105), 1);
        }
        .dark .fi-banner-desc {
            color: rgba(255, 255, 255, 0.55);
        }

        .fi-banner-icon {
            width: 22px;
            height: 22px;
            min-width: 22px;
            border-radius: 999px;
            background: linear-gradient(135deg, #6366f1, #22d3ee);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            margin-top: 2px;
        }

        /* ── Tabs ────────────────────────────────────────────── */
        .fi-tabs-list {
            display: flex;
            gap: 8px;
        }

        .fi-tab-btn {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            color: rgba(var(--gray-500, 107 114 128), 1);
            transition: all 0.2s ease;
        }
        .fi-tab-btn:hover {
            color: rgba(var(--gray-700, 55 65 81), 1);
            background: rgba(var(--gray-100, 243 244 246), 1);
        }
        .fi-tab-btn.fi-tab-active {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }
        .dark .fi-tab-btn {
            color: rgba(255, 255, 255, 0.5);
        }
        .dark .fi-tab-btn:hover {
            color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.08);
        }
        .dark .fi-tab-btn.fi-tab-active {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }

        /* ── Card ────────────────────────────────────────────── */
        .fi-card {
            border: 1px solid rgba(var(--gray-200, 229 231 235), 1);
            border-radius: 12px;
            background: rgba(var(--gray-50, 255 255 255), 1);
        }
        .dark .fi-card {
            border-color: rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
        }

        .fi-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(var(--gray-200, 229 231 235), 1);
            flex-wrap: wrap;
            gap: 8px;
        }
        .dark .fi-toolbar {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .fi-title {
            font-weight: 600;
            color: rgba(var(--gray-950, 15 23 42), 1);
        }
        .dark .fi-title {
            color: rgba(255, 255, 255, 0.95);
        }

        .fi-body {
            padding: 14px;
        }

        /* ── Buttons ─────────────────────────────────────────── */
        .fi-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .fi-btn:hover { opacity: 0.85; }

        .fi-btn-size-sm { padding: 6px 10px; }
        .fi-btn-size-xs { padding: 4px 8px; font-size: 11px; }

        .fi-btn-color-primary {
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            border-color: rgba(99, 102, 241, 0.25);
        }
        .dark .fi-btn-color-primary {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.3);
        }

        .fi-btn-color-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.25);
        }
        .dark .fi-btn-color-success {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .fi-btn-color-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #b45309;
            border-color: rgba(245, 158, 11, 0.25);
        }
        .dark .fi-btn-color-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .fi-btn-color-secondary {
            background: rgba(var(--gray-100, 243 244 246), 1);
            color: rgba(var(--gray-700, 55 65 81), 1);
            border-color: rgba(var(--gray-200, 229 231 235), 1);
        }
        .dark .fi-btn-color-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.8);
            border-color: rgba(255, 255, 255, 0.15);
        }

        /* ── Table ───────────────────────────────────────────── */
        .fi-table-wrap {
            overflow: auto;
            border: 1px solid rgba(var(--gray-200, 229 231 235), 1);
            border-radius: 10px;
        }
        .dark .fi-table-wrap {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .fi-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fi-table th,
        .fi-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(var(--gray-200, 229 231 235), 1);
        }
        .dark .fi-table th,
        .dark .fi-table td {
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        .fi-table th {
            text-align: left;
            font-size: 12px;
            color: rgba(var(--gray-500, 71 85 105), 1);
            font-weight: 600;
        }
        .dark .fi-table th {
            color: rgba(255, 255, 255, 0.55);
        }

        .fi-table td {
            color: rgba(var(--gray-900, 15 23 42), 1);
        }
        .dark .fi-table td {
            color: rgba(255, 255, 255, 0.85);
        }

        .fi-table tbody tr:hover {
            background: rgba(var(--gray-50, 248 250 252), 1);
        }
        .dark .fi-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        /* ── Inputs ──────────────────────────────────────────── */
        .fi-input {
            border: 1px solid rgba(var(--gray-200, 229 231 235), 1);
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            width: 100%;
            background: transparent;
            color: inherit;
            transition: border-color 0.2s ease;
        }
        .fi-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
        }
        .dark .fi-input {
            border-color: rgba(255, 255, 255, 0.15);
        }
        .dark .fi-input:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.2);
        }

        /* ── Badge ───────────────────────────────────────────── */
        .fi-badge {
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .fi-badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }
        .dark .fi-badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
        }

        /* ── Checkbox ────────────────────────────────────────── */
        .fi-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        /* ── Text classes ────────────────────────────────────── */
        .fi-empty {
            color: rgba(var(--gray-500, 71 85 105), 1);
        }
        .dark .fi-empty {
            color: rgba(255, 255, 255, 0.45);
        }

        .fi-tip {
            color: rgba(var(--gray-500, 107 114 128), 1);
            font-size: 13px;
            margin-top: 10px;
        }
        .dark .fi-tip {
            color: rgba(255, 255, 255, 0.4);
        }

        .fi-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: rgba(var(--gray-700, 55 65 81), 1);
            margin-bottom: 8px;
        }
        .dark .fi-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .fi-hint {
            font-size: 13px;
            color: rgba(var(--gray-500, 107 114 128), 1);
            margin-top: 4px;
        }
        .dark .fi-hint {
            color: rgba(255, 255, 255, 0.4);
        }

        .fi-code {
            font-size: 12px;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(var(--gray-100, 243 244 246), 1);
            color: rgba(var(--gray-700, 55 65 81), 1);
        }
        .dark .fi-code {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.7);
        }

        .fi-footer-text {
            font-size: 14px;
            color: rgba(var(--gray-500, 107 114 128), 1);
        }
        .dark .fi-footer-text {
            color: rgba(255, 255, 255, 0.35);
        }

        /* ── Info Box ────────────────────────────────────────── */
        .fi-info-box {
            margin-top: 24px;
            padding: 16px;
            background: rgba(99, 102, 241, 0.05);
            border: 1px solid rgba(99, 102, 241, 0.15);
            border-radius: 12px;
        }
        .dark .fi-info-box {
            background: rgba(99, 102, 241, 0.08);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .fi-info-title {
            font-weight: 600;
            color: #3730a3;
            margin-bottom: 8px;
        }
        .dark .fi-info-title {
            color: #a5b4fc;
        }

        .fi-info-box > div {
            color: rgba(var(--gray-700, 55 65 81), 1);
        }
        .dark .fi-info-box > div {
            color: rgba(255, 255, 255, 0.7);
        }

        .fi-info-tip { color: #4f46e5; }
        .dark .fi-info-tip { color: #a5b4fc; }

        .fi-info-note { color: #b45309; }
        .dark .fi-info-note { color: #fbbf24; }

        .fi-info-muted {
            color: rgba(var(--gray-500, 107 114 128), 1);
        }
        .dark .fi-info-muted {
            color: rgba(255, 255, 255, 0.5);
        }

        .fi-link {
            color: #4f46e5;
            text-decoration: underline;
        }
        .dark .fi-link {
            color: #a5b4fc;
        }
    </style>
</x-filament-panels::page>