@extends('layouts.app')

@section('title', 'SakuPremium - Beranda & Ringkasan Keuangan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- LOGO & QUICK ACTIONS ON DASHBOARD (TABLET/DESKTOP) -->
    <header class="glass-panel" style="padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="logo-container">
            <div class="logo-icon">
                <i data-lucide="wallet" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div class="logo-text">SakuPremium Dashboard</div>
        </div>

        <div class="quick-actions-bar" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary" style="font-size: 0.85rem; padding: 0.6rem 1rem;">
                <i data-lucide="credit-card" style="width: 16px; height: 16px;"></i> Rekening Saya
            </a>
            <a href="{{ route('transactions.index') }}" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.6rem 1rem;">
                <i data-lucide="receipt" style="width: 16px; height: 16px;"></i> Riwayat Transaksi
            </a>
        </div>
    </header>

    <!-- STATS CARD GRID -->
    <div class="grid-3">
        <!-- 1. Consolidated Net Worth -->
        <div class="glass-panel stat-card" style="border-left: 4px solid var(--primary);">
            <div class="stat-header">
                <span>KEKAYAAN BERSIH KONSOLIDASI</span>
                <div class="stat-icon"><i data-lucide="banknote" style="color: var(--primary-light);"></i></div>
            </div>
            <div class="stat-value" style="font-size: 1.8rem; line-height: 1.2; word-break: break-all;">
                Rp {{ number_format($netWorthIdr, 2, ',', '.') }}
            </div>
            
            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Saldo Terpisah:</div>
                <div class="stat-sub" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach ($assetSeparated as $asset)
                        <span style="padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border-color); color: var(--text-secondary); font-size: 0.75rem;">
                            <strong>{{ $asset['symbol'] }} {{ number_format($asset['total'], 2, ',', '.') }}</strong> {{ $asset['code'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div style="margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Konversi Konsolidasi:</div>
                <div class="stat-sub" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach ($netWorthByCurrency as $code => $amount)
                        <span style="padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); background: rgba(139, 92, 246, 0.08); border: 1px solid rgba(139, 92, 246, 0.18); color: #e9d5ff; font-size: 0.75rem;">
                            <strong>{{ $currencies->firstWhere('code', $code)?->symbol ?? $code }} {{ number_format($amount, 2, ',', '.') }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- EXCHANGE RATE UPDATE PANEL -->
            <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: flex; align-items: center; gap: 5px;">
                            <i data-lucide="refresh-cw" style="width: 11px; height: 11px;"></i> Kurs Live
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">
                            @if($lastRateSync)
                                <span style="color: #86efac;">&#x2022; Tersinkron</span>
                                {{ \Carbon\Carbon::parse($lastRateSync)->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                            @else
                                <span style="color: #fca5a5;">&#x2022; Belum disinkron hari ini</span>
                            @endif
                        </div>
                    </div>
                    <form action="{{ route('currencies.update-rates') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary"
                            style="font-size: 0.72rem; padding: 4px 10px; border-radius: 6px; display: flex; align-items: center; gap: 5px; border-color: rgba(139,92,246,0.3);"
                            title="Ambil kurs terbaru dari internet">
                            <i data-lucide="refresh-cw" style="width: 12px; height: 12px;"></i>
                            Perbarui Kurs
                        </button>
                    </form>
                </div>

                <!-- Mini rate display for key currencies -->
                <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.65rem;">
                    @foreach($currencies->whereNotIn('code', ['USD']) as $cur)
                        <span style="font-size: 0.68rem; padding: 2px 7px; border-radius: 20px; background: rgba(255,255,255,0.04); border: 1px solid var(--border-color); color: var(--text-secondary);">
                            1 USD = {{ number_format(1 / ($cur->exchange_rate_to_usd > 0 ? $cur->exchange_rate_to_usd : 1), 2, ',', '.') }} {{ $cur->code }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 2. Cash Flow Ringkasan Bulanan -->
        <div class="glass-panel stat-card" style="border-left: 4px solid var(--secondary);">
            <div class="stat-header">
                <span>RINGKASAN BULAN INI (IDR)</span>
                <div class="stat-icon"><i data-lucide="trending-up" style="color: var(--secondary);"></i></div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.85rem; height: 100%; justify-content: center;">
                <div style="padding: 0.6rem 0.85rem; border-radius: var(--radius-md); background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.15); display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 500;">Pemasukan</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #a7f3d0;">+ Rp {{ number_format($monthlyIncomeIdr, 2, ',', '.') }}</div>
                </div>
                <div style="padding: 0.6rem 0.85rem; border-radius: var(--radius-md); background: rgba(244, 63, 94, 0.06); border: 1px solid rgba(244, 63, 94, 0.15); display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 500;">Pengeluaran</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #fecdd3;">- Rp {{ number_format($monthlyExpenseIdr, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="stat-sub">Persentase Pengeluaran terhadap Pemasukan: 
                <strong>{{ $monthlyIncomeIdr > 0 ? round(($monthlyExpenseIdr / $monthlyIncomeIdr) * 100, 1) : 0 }}%</strong>
            </div>
        </div>

        <!-- 3. Pintasan Quick View Aset Lain -->
        <div class="glass-panel stat-card" style="border-left: 4px solid #eab308;">
            <div class="stat-header">
                <span>PINTASAN PIUTANG & DEPOSIT</span>
                <div class="stat-icon"><i data-lucide="shield-check" style="color: #eab308;"></i></div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 0.75rem; height: 100%; justify-content: center;">
                <a href="{{ route('debts.index') }}" class="glass-card" style="padding: 0.6rem 0.85rem; display: flex; justify-content: space-between; align-items: center; text-decoration: none; border-left: 3px solid var(--accent);">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 500;">Kontrol Hutang Piutang</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 4px;">
                        Buka Menu <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
                <a href="{{ route('deposits.index') }}" class="glass-card" style="padding: 0.6rem 0.85rem; display: flex; justify-content: space-between; align-items: center; text-decoration: none; border-left: 3px solid #eab308;">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); font-weight: 500;">Kawal Uang Jaminan (Deposit)</div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 4px;">
                        Buka Menu <i data-lucide="chevron-right" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
            </div>
            
            <div class="stat-sub">Semua saldo terintegrasi real-time.</div>
        </div>
    </div>

    <!-- MAIN GRID LAYOUT -->
    <div class="grid-12">
        <!-- LEFT: STATISTIK KATEGORI BULANAN (8 Cols) -->
        <div class="span-8">
            <section class="glass-panel" style="padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="section-title" style="margin: 0;">
                    <span>Alokasi Pengeluaran Bulanan per Kategori (IDR)</span>
                    <i data-lucide="bar-chart-3" style="color: var(--text-secondary);"></i>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    @forelse($expenseCategoryStats as $stat)
                        <div class="glass-card" style="padding: 1rem 1.25rem;">
                            <div style="display: flex; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem; font-size: 0.85rem;">
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                                    <span style="width: 9px; height: 9px; border-radius: 999px; background: {{ $stat['color'] }}; box-shadow: 0 0 8px {{ $stat['color'] }};"></span>
                                    {{ $stat['name'] }}
                                </span>
                                <span style="color: var(--text-secondary); font-weight: 600;">{{ $stat['percentage'] }}%</span>
                            </div>
                            <div style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.6rem;">{{ $stat['formatted'] }}</div>
                            <div style="height: 6px; border-radius: 999px; background: rgba(255, 255, 255, 0.05); overflow: hidden;">
                                <div style="height: 100%; width: {{ $stat['percentage'] }}%; background: linear-gradient(90deg, {{ $stat['color'] }}, rgba(255, 255, 255, 0.6)); border-radius: 999px;"></div>
                            </div>
                        </div>
                    @empty
                        <div class="glass-card" style="grid-column: 1 / -1; padding: 2rem; text-align: center; color: var(--text-muted);">
                            <i data-lucide="inbox" style="width: 32px; height: 32px; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                            <p>Belum ada catatan pengeluaran dengan kategori tertentu di bulan berjalan ini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- RIGHT: TRANSAKSI TERBARU & RINCIAN KAS (4 Cols) -->
        <div class="span-4">
            <section class="glass-panel" style="padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; height: 100%;">
                <div class="section-title" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                    <span>Aktifitas Terkini</span>
                    <i data-lucide="history" style="color: var(--text-secondary);"></i>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem; flex-grow: 1;">
                    @forelse($recentTransactions as $tx)
                        <div class="glass-card" style="padding: 0.85rem 1rem; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">
                                    {{ $tx->account->name }}
                                </span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">
                                    {{ $tx->transaction_date->format('d M Y') }} · {{ $tx->description ?? 'Kas Flow' }}
                                </span>
                            </div>
                            <div style="text-align: right;">
                                @if($tx->type === 'income')
                                    <span class="amount-income" style="font-size: 0.9rem;">+{{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 0, ',', '.') }}</span>
                                @elseif($tx->type === 'expense')
                                    <span class="amount-expense" style="font-size: 0.9rem;">-{{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="amount-transfer" style="font-size: 0.9rem;">⇄{{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 2rem 0; margin-top: auto; margin-bottom: auto;">
                            Belum ada aktifitas transaksi dicatat.
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('transactions.index') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: auto;">
                    Lihat Semua Ledger Transaksi <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
                </a>
            </section>
        </div>
    </div>
</div>
@endsection
