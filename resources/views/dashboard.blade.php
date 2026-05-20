<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SakuPremium - Catatan Keuangan Masa Kini</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <div class="container">
        <!-- HEADER -->
        <header class="glass-panel" style="padding: 1.25rem 2rem; margin-bottom: 2rem;">
            <div class="logo-container">
                <div class="logo-icon">
                    <i data-lucide="wallet" style="color: white; width: 24px; height: 24px;"></i>
                </div>
                <div class="logo-text">SakuPremium</div>
            </div>
            
            <div class="quick-actions-bar">
                <button class="btn btn-secondary" onclick="openModal('modalAccount')">
                    <i data-lucide="plus-circle"></i> Rekening Baru
                </button>
                <button class="btn btn-secondary" onclick="openModal('modalContact')">
                    <i data-lucide="user-plus"></i> Kontak Baru
                </button>
                <button class="btn btn-primary" onclick="openModal('modalTransaction')">
                    <i data-lucide="receipt"></i> Catat Transaksi
                </button>
                <button class="btn btn-secondary" style="border-color: rgba(244, 63, 94, 0.4); color: #fca5a5;" onclick="openModal('modalDebt')">
                    <i data-lucide="users"></i> Hutang / Piutang
                </button>
                <button class="btn btn-secondary" style="border-color: rgba(234, 179, 8, 0.4); color: #fef08a;" onclick="openModal('modalDeposit')">
                    <i data-lucide="shield-check"></i> Uang Deposit
                </button>
            </div>
        </header>

        <!-- ALERTS -->
        @if(session('success'))
            <div class="alert alert-success">
                <i data-lucide="check-circle-2"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i data-lucide="alert-triangle"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- STATS CARD GRID -->
        <div class="grid-3" style="margin-bottom: 2.5rem;">
            <!-- Net Worth -->
            <div class="glass-panel stat-card" style="border-left: 4px solid var(--primary);">
                <div class="stat-header">
                    <span>KEKAYAAN BERSIH KONSOLIDASI</span>
                    <div class="stat-icon"><i data-lucide="banknote" style="color: var(--primary-light);"></i></div>
                </div>
                <div class="stat-value">Rp {{ number_format($netWorthIdr, 2, ',', '.') }}</div>
                <div class="stat-sub">Semua mata uang dikonversi ke IDR (Rate: 1 TWD = Rp 500, 1 USD = Rp 16.000)</div>
            </div>

            <!-- Active Debts -->
            <div class="glass-panel stat-card" style="border-left: 4px solid var(--accent);">
                <div class="stat-header">
                    <span>HUTANG & PIUTANG PENDING</span>
                    <div class="stat-icon"><i data-lucide="landmark" style="color: var(--accent);"></i></div>
                </div>
                <div class="stat-value" style="font-size: 1.6rem; display: flex; flex-direction: column; gap: 4px; justify-content: center; height: 100%;">
                    <div style="color: #f43f5e; font-size: 1.15rem; font-weight: 600;">
                        Hutang Kita: Rp {{ number_format($debts->where('type', 'debt')->sum(fn($d) => $d->account->currency->code == 'TWD' ? $d->remaining_amount * 500 : $d->remaining_amount), 2, ',', '.') }}
                    </div>
                    <div style="color: #10b981; font-size: 1.15rem; font-weight: 600;">
                        Piutang Kita: Rp {{ number_format($debts->where('type', 'receivable')->sum(fn($d) => $d->account->currency->code == 'TWD' ? $d->remaining_amount * 500 : $d->remaining_amount), 2, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Active Deposits -->
            <div class="glass-panel stat-card" style="border-left: 4px solid #eab308;">
                <div class="stat-header">
                    <span>DEPOSIT JAMINAN AKTIF</span>
                    <div class="stat-icon"><i data-lucide="shield-check" style="color: #eab308;"></i></div>
                </div>
                <div class="stat-value">Rp {{ number_format($deposits->sum(fn($d) => $d->account->currency->code == 'TWD' ? $d->amount * 500 : $d->amount), 2, ',', '.') }}</div>
                <div class="stat-sub">{{ $deposits->count() }} deposit jaminan aktif di pihak ketiga</div>
            </div>
        </div>

        <!-- MAIN CONTENT (12 Cols) -->
        <div class="grid-12">
            <!-- LEFT SECTION (8 Cols) -->
            <div class="span-8" style="display: flex; flex-direction: column; gap: 2.5rem;">
                
                <!-- REKENING KEUANGAN -->
                <section class="glass-panel" style="padding: 2rem;">
                    <div class="section-title">
                        <span>Rekening & Dompet Anda</span>
                        <i data-lucide="credit-card" style="color: var(--text-secondary);"></i>
                    </div>
                    
                    <div class="accounts-grid">
                        @foreach($accounts as $acc)
                            <div class="glass-card account-pill {{ $acc->type }}">
                                <div class="account-name">
                                    {{ $acc->name }}
                                    <span class="currency-badge">{{ $acc->currency_code }}</span>
                                </div>
                                <div class="account-number">{{ $acc->account_number ?? 'Uang Tunai / Cash' }}</div>
                                <div class="account-balance">
                                    <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 500;">
                                        {{ $acc->currency->symbol }}
                                    </span>
                                    {{ number_format($acc->balance, 2, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- TRANSAKSI TERBARU -->
                <section class="glass-panel" style="padding: 2rem;">
                    <div class="section-title">
                        <span>Aktivitas Transaksi Terbaru</span>
                        <i data-lucide="history" style="color: var(--text-secondary);"></i>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Rekening</th>
                                    <th>Tipe</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th style="text-align: right;">Jumlah</th>
                                    <th style="text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                    <tr>
                                        <td style="color: var(--text-secondary);">{{ $tx->transaction_date->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div style="font-weight: 600;">{{ $tx->account->name }}</div>
                                            @if($tx->type === 'transfer')
                                                <div style="font-size: 0.75rem; color: var(--text-secondary); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                                    <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                                                    {{ $tx->destinationAccount->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $tx->type }}">
                                                {{ $tx->type === 'transfer' ? 'transfer' : ($tx->type === 'income' ? 'masuk' : 'keluar') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tx->category)
                                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $tx->category->color }}"></span>
                                                    {{ $tx->category->name }}
                                                </span>
                                            @else
                                                <span style="color: var(--text-muted);">Internal / Cash Flow</span>
                                            @endif
                                        </td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $tx->description }}">
                                            {{ $tx->description ?? '-' }}
                                        </td>
                                        <td style="text-align: right;">
                                            @if($tx->type === 'income')
                                                <span class="amount-income">+ {{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 2, ',', '.') }}</span>
                                            @elseif($tx->type === 'expense')
                                                <span class="amount-expense">- {{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 2, ',', '.') }}</span>
                                            @elseif($tx->type === 'transfer')
                                                <div class="amount-transfer">- {{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 2, ',', '.') }}</div>
                                                <div style="font-size: 0.75rem; color: #22d3ee; margin-top: 2px;">
                                                    + {{ $tx->destinationAccount->currency->symbol }} {{ number_format($tx->destination_amount, 2, ',', '.') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Saldo rekening akan dikembalikan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" style="padding: 4px 8px; font-size: 0.75rem; border-radius: 4px;">
                                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">Belum ada aktivitas transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- RIGHT SECTION (4 Cols) -->
            <div class="span-4" style="display: flex; flex-direction: column; gap: 2.5rem;">
                
                <!-- HUTANG & PIUTANG -->
                <section class="glass-panel" style="padding: 2rem;">
                    <div class="section-title">
                        <span>Hutang & Piutang</span>
                        <i data-lucide="users" style="color: var(--text-secondary);"></i>
                    </div>

                    <div class="tab-container">
                        <button class="tab-btn active" onclick="switchTab(event, 'tab-hutang')">Hutang Kita</button>
                        <button class="tab-btn" onclick="switchTab(event, 'tab-piutang')">Piutang Kita</button>
                        <button class="btn btn-success" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 4px; margin-left: auto;" onclick="openModal('modalRepayment')">
                            Bayar Cicilan
                        </button>
                    </div>

                    <!-- TAB HUTANG -->
                    <div id="tab-hutang" class="tab-pane active">
                        <div class="list-cards">
                            @forelse($debts->where('type', 'debt') as $d)
                                <div class="list-item-card">
                                    <div class="item-left">
                                        <span class="item-title">{{ $d->contact->name }}</span>
                                        <span class="item-subtitle">Melalui: {{ $d->account->name }}</span>
                                        @if($d->due_date)
                                            <span class="item-subtitle" style="color: #fca5a5;">Jatuh tempo: {{ $d->due_date->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                    <div class="item-right">
                                        <span class="item-amount debt">{{ $d->account->currency->symbol }} {{ number_format($d->remaining_amount, 2, ',', '.') }}</span>
                                        <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #fca5a5; font-size: 0.65rem; padding: 2px 6px;">Pending</span>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: var(--text-muted); padding: 1.5rem 0;">Tidak ada hutang pending.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB PIUTANG -->
                    <div id="tab-piutang" class="tab-pane">
                        <div class="list-cards">
                            @forelse($debts->where('type', 'receivable') as $d)
                                <div class="list-item-card">
                                    <div class="item-left">
                                        <span class="item-title">{{ $d->contact->name }}</span>
                                        <span class="item-subtitle">Melalui: {{ $d->account->name }}</span>
                                        @if($d->due_date)
                                            <span class="item-subtitle" style="color: #a7f3d0;">Jatuh tempo: {{ $d->due_date->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                    <div class="item-right">
                                        <span class="item-amount receivable">{{ $d->account->currency->symbol }} {{ number_format($d->remaining_amount, 2, ',', '.') }}</span>
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #a7f3d0; font-size: 0.65rem; padding: 2px 6px;">Pending</span>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: var(--text-muted); padding: 1.5rem 0;">Tidak ada piutang pending.</div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <!-- DEPOSIT JAMINAN -->
                <section class="glass-panel" style="padding: 2rem;">
                    <div class="section-title">
                        <span>Uang Deposit</span>
                        <i data-lucide="shield-check" style="color: var(--text-secondary);"></i>
                    </div>

                    <div style="margin-bottom: 1.5rem; display: flex; justify-content: flex-end;">
                        <button class="btn btn-success" style="padding: 4px 10px; font-size: 0.75rem; border-radius: 4px;" onclick="openModal('modalDepositReturn')">
                            Kembalikan Deposit
                        </button>
                    </div>

                    <div class="list-cards">
                        @forelse($deposits as $dep)
                            <div class="list-item-card">
                                <div class="item-left">
                                    <span class="item-title">{{ $dep->name }}</span>
                                    <span class="item-subtitle">Dari: {{ $dep->account->name }}</span>
                                    @if($dep->description)
                                        <span class="item-subtitle">{{ $dep->description }}</span>
                                    @endif
                                </div>
                                <div class="item-right">
                                    <span class="item-amount" style="color: #fef08a;">{{ $dep->account->currency->symbol }} {{ number_format($dep->amount, 2, ',', '.') }}</span>
                                    <span class="badge" style="background: rgba(234, 179, 8, 0.1); color: #fef08a; font-size: 0.65rem; padding: 2px 6px;">Aktif</span>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); padding: 1.5rem 0;">Tidak ada deposit aktif.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- MODALS CONTAINER -->

    <!-- 1. Modal Tambah Akun -->
    <div id="modalAccount" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalAccount')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Rekening Baru</h3>
                <button class="close-btn" onclick="closeModal('modalAccount')">&times;</button>
            </div>
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Akun/Rekening</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Bank Mandiri, EasyCard" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Rekening (Opsional)</label>
                    <input type="text" name="account_number" class="form-control" placeholder="Contoh: 1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Akun</label>
                    <select name="type" class="form-control" required>
                        <option value="bank">Bank / Rekening Koran</option>
                        <option value="cash">Uang Tunai (Cash)</option>
                        <option value="card">Kartu Prabayar (EasyCard, dll)</option>
                        <option value="e_wallet">Dompet Digital (GoPay, LinePay, dll)</option>
                        <option value="deposit">Deposit Jaminan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mata Uang</label>
                    <select name="currency_code" class="form-control" required>
                        @foreach($currencies as $c)
                            <option value="{{ $c->code }}">{{ $c->code }} - {{ $c->name }} ({{ $c->symbol }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Saldo Awal</label>
                    <input type="number" name="balance" class="form-control" value="0" step="0.01" min="0" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Akun</button>
            </form>
        </div>
    </div>

    <!-- 2. Modal Tambah Kontak -->
    <div id="modalContact" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalContact')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Kontak Baru</h3>
                <button class="close-btn" onclick="closeModal('modalContact')">&times;</button>
            </div>
            <form action="{{ route('contacts.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Prasetyo" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Telepon (Opsional)</label>
                    <input type="text" name="phone" class="form-control" placeholder="Contoh: 08123456789">
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan (Opsional)</label>
                    <textarea name="description" class="form-control" placeholder="Hubungan teman, ibu kos, dll" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Kontak</button>
            </form>
        </div>
    </div>

    <!-- 3. Modal Catat Transaksi -->
    <div id="modalTransaction" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalTransaction')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Catat Transaksi Baru</h3>
                <button class="close-btn" onclick="closeModal('modalTransaction')">&times;</button>
            </div>
            
            <div class="tab-container" style="margin-bottom: 1.25rem;">
                <button class="tab-btn active" id="tx-tab-btn-expense" onclick="switchTxType('expense')">Pengeluaran</button>
                <button class="tab-btn" id="tx-tab-btn-income" onclick="switchTxType('income')">Pemasukan</button>
                <button class="tab-btn" id="tx-tab-btn-transfer" onclick="switchTxType('transfer')">Transfer Akun</button>
            </div>

            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" id="tx-type-input" value="expense">

                <div class="form-group" id="group-source-account">
                    <label class="form-label" id="label-source-account">Rekening Asal (Pengeluaran)</label>
                    <select name="account_id" class="form-control" required>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: {{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="group-destination-account" style="display: none;">
                    <label class="form-label">Rekening Tujuan</label>
                    <select name="destination_account_id" class="form-control">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: {{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="group-amount">
                    <label class="form-label" id="label-amount">Nominal</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="Masukkan jumlah" required>
                </div>

                <div class="form-group" id="group-destination-amount" style="display: none;">
                    <label class="form-label">Nominal Diterima Rekening Tujuan (Untuk Transfer Lintas Mata Uang)</label>
                    <input type="number" name="destination_amount" class="form-control" step="0.01" min="0.01" placeholder="Masukkan jumlah yang diterima">
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Biarkan kosong jika transfer dalam mata uang yang sama (akan disamakan otomatis).</small>
                </div>

                <div class="form-group" id="group-category">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" id="select-category" class="form-control">
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="datetime-local" name="transaction_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Keterangan</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Beli makan siang, Transfer uang saku">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Transaksi</button>
            </form>
        </div>
    </div>

    <!-- 4. Modal Catat Hutang/Piutang -->
    <div id="modalDebt" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDebt')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Catat Hutang / Piutang Baru</h3>
                <button class="close-btn" onclick="closeModal('modalDebt')">&times;</button>
            </div>
            <form action="{{ route('debts.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Kontak Debitur/Kreditur</label>
                    <select name="contact_id" class="form-control" required>
                        @forelse($contacts as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone ?? 'Tanpa Nomor' }})</option>
                        @empty
                            <option disabled>Silakan buat kontak terlebih dahulu!</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Catatan</label>
                    <select name="type" class="form-control" required>
                        <option value="debt">Hutang (Kita meminjam uang / Dana MASUK)</option>
                        <option value="receivable">Piutang (Kita meminjamkan uang / Dana KELUAR)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rekening Transaksi</label>
                    <select name="account_id" class="form-control" required>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nominal Pinjaman</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="Contoh: 150000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Jatuh Tempo (Opsional)</label>
                    <input type="date" name="due_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan / Alasan Pinjaman</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Pinjaman modal usaha, bayar sewa">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Hutang/Piutang</button>
            </form>
        </div>
    </div>

    <!-- 5. Modal Pelunasan Cicilan -->
    <div id="modalRepayment" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalRepayment')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Catat Pembayaran Cicilan / Pelunasan</h3>
                <button class="close-btn" onclick="closeModal('modalRepayment')">&times;</button>
            </div>
            <form action="{{ route('debts.repayment') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Pilih Catatan Hutang / Piutang</label>
                    <select name="debt_id" class="form-control" required>
                        @forelse($debts as $d)
                            <option value="{{ $d->id }}">
                                [{{ strtoupper($d->type === 'debt' ? 'Hutang' : 'Piutang') }}] {{ $d->contact->name }} - 
                                Sisa: {{ $d->account->currency->symbol }} {{ number_format($d->remaining_amount, 2) }}
                            </option>
                        @empty
                            <option disabled>Tidak ada catatan hutang/piutang pending.</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pilih Rekening Pembayaran</label>
                    <select name="account_id" class="form-control" required>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: {{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nominal Pembayaran / Cicilan</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="Masukkan jumlah" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Pembayaran cicilan pertama, Pelunasan lunas">
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 1rem;">Catat Pembayaran</button>
            </form>
        </div>
    </div>

    <!-- 6. Modal Catat Deposit -->
    <div id="modalDeposit" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDeposit')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Catat Uang Deposit Jaminan Baru</h3>
                <button class="close-btn" onclick="closeModal('modalDeposit')">&times;</button>
            </div>
            <form action="{{ route('deposits.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama/Keperluan Deposit</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Deposit Sewa Kost, Sewa Sepeda" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Rekening Sumber Pembayaran (Uang Keluar)</label>
                    <select name="account_id" class="form-control" required>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: {{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nominal Uang Jaminan (Deposit)</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="Masukkan nominal" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan Lainnya</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Deposit sewa kamar 3 bulan, bisa dikembalikan">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Deposit</button>
            </form>
        </div>
    </div>

    <!-- 7. Modal Pengembalian Deposit -->
    <div id="modalDepositReturn" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDepositReturn')">
        <div class="modal-content glass-panel">
            <div class="modal-header">
                <h3 class="modal-title">Catat Pengembalian Uang Deposit</h3>
                <button class="close-btn" onclick="closeModal('modalDepositReturn')">&times;</button>
            </div>
            <form action="{{ route('deposits.return') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Pilih Uang Deposit Jaminan</label>
                    <select name="deposit_id" class="form-control" required>
                        @forelse($deposits as $dep)
                            <option value="{{ $dep->id }}">
                                {{ $dep->name }} - {{ $dep->account->currency->symbol }} {{ number_format($dep->amount, 2) }}
                            </option>
                        @empty
                            <option disabled>Tidak ada deposit jaminan yang aktif.</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rekening Penerima Pengembalian (Uang Masuk)</label>
                    <select name="account_id" class="form-control" required>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Saldo: {{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan (Opsional)</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Dikembalikan lunas secara tunai">
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 1rem;">Catat Pengembalian</button>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT FOR INTERACTIVENESS -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // 1. Modals Control
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function closeModalOnOverlay(event, modalId) {
            if (event.target === document.getElementById(modalId)) {
                closeModal(modalId);
            }
        }

        // 2. Debts and Receivables Tab switcher
        function switchTab(evt, tabId) {
            // Get all tab elements
            const tabPanes = document.getElementsByClassName("tab-pane");
            for (let i = 0; i < tabPanes.length; i++) {
                tabPanes[i].classList.remove("active");
            }

            const tabBtns = document.getElementsByClassName("tab-btn");
            for (let i = 0; i < tabBtns.length; i++) {
                // Ignore transaction modal tabs
                if (!tabBtns[i].id.startsWith("tx-tab-btn-")) {
                    tabBtns[i].classList.remove("active");
                }
            }

            // Show active tab and set active button
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        // 3. Transactions Modal tab switcher (Dynamic Forms)
        function switchTxType(type) {
            // Reset active state
            document.getElementById("tx-tab-btn-expense").classList.remove("active");
            document.getElementById("tx-tab-btn-income").classList.remove("active");
            document.getElementById("tx-tab-btn-transfer").classList.remove("active");

            // Set active class
            document.getElementById(`tx-tab-btn-${type}`).classList.add("active");

            // Set input value
            document.getElementById("tx-type-input").value = type;

            // Form element visibility logic
            const grpDestAccount = document.getElementById("group-destination-account");
            const grpDestAmount = document.getElementById("group-destination-amount");
            const grpCategory = document.getElementById("group-category");
            const lblSourceAccount = document.getElementById("label-source-account");
            
            const incomeCats = [
                @foreach($incomeCategories as $cat)
                    { id: {{ $cat->id }}, name: "{{ $cat->name }}" },
                @endforeach
            ];

            const expenseCats = [
                @foreach($expenseCategories as $cat)
                    { id: {{ $cat->id }}, name: "{{ $cat->name }}" },
                @endforeach
            ];

            const selectCat = document.getElementById("select-category");

            if (type === 'transfer') {
                grpDestAccount.style.display = "block";
                grpDestAmount.style.display = "block";
                grpCategory.style.display = "none";
                lblSourceAccount.textContent = "Rekening Asal (Uang Keluar)";
                
                // Remove required from category select
                selectCat.removeAttribute("required");
            } else {
                grpDestAccount.style.display = "none";
                grpDestAmount.style.display = "none";
                grpCategory.style.display = "block";
                selectCat.setAttribute("required", "required");

                // Populate categories dropdown based on type
                selectCat.innerHTML = "";
                const currentCats = (type === 'income') ? incomeCats : expenseCats;
                lblSourceAccount.textContent = (type === 'income') ? "Rekening Penerima (Pemasukan)" : "Rekening Asal (Pengeluaran)";

                currentCats.forEach(cat => {
                    const opt = document.createElement("option");
                    opt.value = cat.id;
                    opt.textContent = cat.name;
                    selectCat.appendChild(opt);
                });
            }
        }
    </script>
</body>
</html>
