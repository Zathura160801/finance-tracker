@extends('layouts.app')

@section('title', 'SakuPremium - Riwayat Ledger Transaksi Lengkap')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- HEADER -->
    <header class="glass-panel" style="padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="logo-container">
            <div class="logo-icon">
                <i data-lucide="receipt" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div class="logo-text">Ledger Riwayat Transaksi</div>
        </div>

        <button class="btn btn-primary" onclick="openCreateTransactionModal()">
            <i data-lucide="plus-circle"></i> Catat Transaksi Baru
        </button>
    </header>

    <!-- FILTER BOARD -->
    <section class="glass-panel" style="padding: 1.5rem 2rem;">
        <div class="section-title" style="font-size: 1.05rem; margin-bottom: 1.25rem;">
            <span>Saring & Cari Transaksi</span>
            <i data-lucide="sliders-horizontal" style="width: 18px; height: 18px; color: var(--text-secondary);"></i>
        </div>

        <form method="GET" action="{{ route('transactions.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: flex-end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Akun Keuangan</label>
                <select name="account_id" class="form-control">
                    <option value="">Semua Akun / Rekening</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(($transactionFilters['account_id'] ?? null) == $acc->id)>
                            {{ $acc->name }} ({{ $acc->currency_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Tipe Kas Flow</label>
                <select name="type" class="form-control">
                    <option value="">Semua Aliran</option>
                    <option value="income" @selected(($transactionFilters['type'] ?? null) === 'income')>Pemasukan (Masuk)</option>
                    <option value="expense" @selected(($transactionFilters['type'] ?? null) === 'expense')>Pengeluaran (Keluar)</option>
                    <option value="transfer" @selected(($transactionFilters['type'] ?? null) === 'transfer')>Transfer Antar Rekening</option>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-control">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($transactionFilters['category_id'] ?? null) == $category->id)>
                            {{ $category->name }} ({{ ucfirst($category->type) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Dari Tanggal</label>
                <input type="text" name="date_from" id="filter-date-from" class="form-control datepicker-date" value="{{ $transactionFilters['date_from'] ?? '' }}" placeholder="Pilih tanggal...">
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">Hingga Tanggal</label>
                <input type="text" name="date_to" id="filter-date-to" class="form-control datepicker-date" value="{{ $transactionFilters['date_to'] ?? '' }}" placeholder="Pilih tanggal...">
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary" style="flex-grow: 1;">Filter</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary" style="flex-grow: 1;">Reset</a>
            </div>
        </form>
    </section>

    <!-- LEDGER TABLE -->
    <section class="glass-panel" style="padding: 2rem;">
        <div class="section-title">
            <span>Daftar Arus Transaksi Keuangan</span>
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Menampilkan {{ $transactions->count() }} transaksi halaman ini</span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal & Jam</th>
                        <th>Akun / Rekening</th>
                        <th>Tipe Aliran</th>
                        <th>Kategori</th>
                        <th>Catatan Keterangan</th>
                        <th style="text-align: right;">Jumlah Transaksi</th>
                        <th style="text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 500;">
                                {{ $tx->transaction_date->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.9rem;">{{ $tx->account->name }}</div>
                                @if ($tx->type === 'transfer' && $tx->destinationAccount)
                                    <div style="font-size: 0.75rem; color: var(--text-secondary); display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                        <i data-lucide="arrow-right" style="width: 12px; height: 12px; color: var(--secondary);"></i>
                                        {{ $tx->destinationAccount->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $tx->type }}">
                                    {{ $tx->type === 'transfer' ? 'Transfer' : ($tx->type === 'income' ? 'Masuk' : 'Keluar') }}
                                </span>
                            </td>
                            <td>
                                @if ($tx->category)
                                    <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $tx->category->color }}; box-shadow: 0 0 6px {{ $tx->category->color }};"></span>
                                        {{ $tx->category->name }}
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Internal / Non-Kat</span>
                                @endif
                            </td>
                            <td style="max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.85rem;" title="{{ $tx->description }}">
                                {{ $tx->description ?? '-' }}
                            </td>
                            <td style="text-align: right; font-size: 0.95rem;">
                                @if ($tx->type === 'income')
                                    <span class="amount-income">+ {{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 2, ',', '.') }}</span>
                                @elseif($tx->type === 'expense')
                                    <span class="amount-expense">- {{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 2, ',', '.') }}</span>
                                @elseif($tx->type === 'transfer')
                                    <div class="amount-transfer" style="font-weight: 600;">- {{ $tx->account->currency->symbol }} {{ number_format($tx->amount, 2, ',', '.') }}</div>
                                    @if($tx->destinationAccount)
                                        <div style="font-size: 0.75rem; color: #22d3ee; margin-top: 2px; font-weight: 500;">
                                            + {{ $tx->destinationAccount->currency->symbol }} {{ number_format($tx->destination_amount, 2, ',', '.') }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display:flex; gap:6px; justify-content:center; align-items:center;">
                                    <button type="button" class="btn btn-secondary" style="padding: 5px 8px; font-size: 0.75rem; border-radius: 6px;"
                                        onclick="openTransactionEditModal(this)"
                                        data-id="{{ $tx->id }}" 
                                        data-type="{{ $tx->type }}"
                                        data-account_id="{{ $tx->account_id }}"
                                        data-destination_account_id="{{ $tx->destination_account_id ?? '' }}"
                                        data-amount="{{ $tx->amount }}"
                                        data-destination_amount="{{ $tx->destination_amount ?? '' }}"
                                        data-category_id="{{ $tx->category_id ?? '' }}"
                                        data-transaction_date="{{ $tx->transaction_date->format('Y-m-d\TH:i') }}"
                                        data-description="{{ htmlspecialchars($tx->description ?? '', ENT_QUOTES) }}">
                                        <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                    </button>

                                    <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini? Saldo rekening akan secara otomatis disesuaikan (dibalik).')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" style="padding: 5px 8px; font-size: 0.75rem; border-radius: 6px;">
                                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
                                <i data-lucide="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <p style="font-size: 1.1rem; font-weight: 500;">Tidak ditemukan transaksi</p>
                                <p style="font-size: 0.85rem; margin-top: 4px;">Tidak ada transaksi yang cocok dengan filter pencarian Anda atau ledger masih kosong.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION NAVIGATION LINKS -->
        @if ($transactions->hasPages())
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-top: 1.5rem;">
                @if ($transactions->onFirstPage())
                    <span class="btn btn-secondary" style="opacity: 0.4; pointer-events: none; padding: 0.5rem 1rem;">Sebelumnya</span>
                @else
                    <a class="btn btn-secondary" href="{{ $transactions->previousPageUrl() }}" style="padding: 0.5rem 1rem;">Sebelumnya</a>
                @endif

                @foreach ($transactions->getUrlRange(1, $transactions->lastPage()) as $page => $url)
                    @if ($page === $transactions->currentPage())
                        <span class="btn btn-primary" style="min-width: 40px; padding: 0.5rem 0.85rem;">{{ $page }}</span>
                    @else
                        <a class="btn btn-secondary" style="min-width: 40px; padding: 0.5rem 0.85rem;" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($transactions->hasMorePages())
                    <a class="btn btn-secondary" href="{{ $transactions->nextPageUrl() }}" style="padding: 0.5rem 1rem;">Berikutnya</a>
                @else
                    <span class="btn btn-secondary" style="opacity: 0.4; pointer-events: none; padding: 0.5rem 1rem;">Berikutnya</span>
                @endif
            </div>
        @endif
    </section>
</div>

<!-- MODALS -->

<!-- 1. Modal Create Transaction -->
<div id="modalTransaction" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalTransaction')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-transaction-title">Catat Transaksi Baru</h3>
            <button class="close-btn" onclick="closeModal('modalTransaction')">&times;</button>
        </div>
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Tipe Aliran Uang</label>
                <select name="type" id="tx-type" class="form-control" onchange="toggleTransferFields('tx')" required>
                    <option value="expense">Pengeluaran (Keluar)</option>
                    <option value="income">Pemasukan (Masuk)</option>
                    <option value="transfer">Transfer Antar Rekening</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" id="tx-account-label">Sumber Rekening / Dompet</label>
                <select name="account_id" id="tx-account" class="form-control" required>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Destination Account (For Transfer Only) -->
            <div class="form-group" id="tx-destination-group" style="display: none;">
                <label class="form-label">Rekening / Dompet Tujuan</label>
                <select name="destination_account_id" id="tx-destination" class="form-control">
                    <option value="">Pilih rekening tujuan...</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" id="tx-amount-label">Jumlah Uang</label>
                <input type="number" name="amount" id="tx-amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
            </div>

            <!-- Destination Amount (For Cross-Currency Transfer Only) -->
            <div class="form-group" id="tx-dest-amount-group" style="display: none;">
                <label class="form-label">Jumlah Uang Diterima (Tujuan - Opsional)</label>
                <input type="number" name="destination_amount" id="tx-dest-amount" class="form-control" step="0.01" min="0.01" placeholder="Kosongkan jika mata uang sama">
            </div>

            <div class="form-group" id="tx-category-group">
                <label class="form-label">Kategori Transaksi</label>
                <select name="category_id" id="tx-category" class="form-control">
                    <option value="">Pilih kategori (Opsional)...</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }} ({{ ucfirst($category->type) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Transaksi</label>
                <input type="text" name="transaction_date" id="tx-date" class="form-control datepicker-datetime" placeholder="Pilih tanggal & jam...">
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan Catatan</label>
                <textarea name="description" id="tx-description" class="form-control" rows="3" placeholder="Contoh: Beli makan siang, Transfer tabungan bulanan"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Transaksi</button>
        </form>
    </div>
</div>

<!-- 2. Modal Edit Transaction -->
<div id="modalTransactionEdit" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalTransactionEdit')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Edit Detail Transaksi</h3>
            <button class="close-btn" onclick="closeModal('modalTransactionEdit')">&times;</button>
        </div>
        <form id="form-transaction-edit" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Tipe Aliran Uang</label>
                <select name="type" id="edit-tx-type" class="form-control" onchange="toggleTransferFields('edit-tx')" required>
                    <option value="expense">Pengeluaran (Keluar)</option>
                    <option value="income">Pemasukan (Masuk)</option>
                    <option value="transfer">Transfer Antar Rekening</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" id="edit-tx-account-label">Sumber Rekening / Dompet</label>
                <select name="account_id" id="edit-tx-account" class="form-control" required>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Destination Account (For Transfer Only) -->
            <div class="form-group" id="edit-tx-destination-group" style="display: none;">
                <label class="form-label">Rekening / Dompet Tujuan</label>
                <select name="destination_account_id" id="edit-tx-destination" class="form-control">
                    <option value="">Pilih rekening tujuan...</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency_code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" id="edit-tx-amount-label">Jumlah Uang</label>
                <input type="number" name="amount" id="edit-tx-amount" class="form-control" step="0.01" min="0.01" required>
            </div>

            <!-- Destination Amount (For Cross-Currency Transfer Only) -->
            <div class="form-group" id="edit-tx-dest-amount-group" style="display: none;">
                <label class="form-label">Jumlah Uang Diterima (Tujuan - Opsional)</label>
                <input type="number" name="destination_amount" id="edit-tx-dest-amount" class="form-control" step="0.01" min="0.01">
            </div>

            <div class="form-group" id="edit-tx-category-group">
                <label class="form-label">Kategori Transaksi</label>
                <select name="category_id" id="edit-tx-category" class="form-control">
                    <option value="">Pilih kategori (Opsional)...</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }} ({{ ucfirst($category->type) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Transaksi</label>
                <input type="text" name="transaction_date" id="edit-tx-date" class="form-control datepicker-datetime" placeholder="Pilih tanggal & jam..." required>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan Catatan</label>
                <textarea name="description" id="edit-tx-description" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleTransferFields(prefix) {
        const typeEl = document.getElementById(`${prefix}-type`);
        const destGroup = document.getElementById(`${prefix}-destination-group`);
        const destAmountGroup = document.getElementById(`${prefix}-dest-amount-group`);
        const catGroup = document.getElementById(`${prefix}-category-group`);
        const accountLabel = document.getElementById(`${prefix}-account-label`);
        const amountLabel = document.getElementById(`${prefix}-amount-label`);

        if (typeEl.value === 'transfer') {
            destGroup.style.display = 'block';
            destAmountGroup.style.display = 'block';
            catGroup.style.display = 'none';
            accountLabel.innerText = 'Rekening Asal (Sumber)';
            amountLabel.innerText = 'Jumlah Uang Dikirim';
            
            // Destination fields required
            document.getElementById(`${prefix}-destination`).setAttribute('required', 'required');
        } else {
            destGroup.style.display = 'none';
            destAmountGroup.style.display = 'none';
            catGroup.style.display = 'block';
            accountLabel.innerText = 'Sumber Rekening / Dompet';
            amountLabel.innerText = 'Jumlah Uang';
            
            document.getElementById(`${prefix}-destination`).removeAttribute('required');
        }
    }

    function openCreateTransactionModal() {
        // Set date to current time via Flatpickr API
        const txDatePicker = document.getElementById('tx-date')._flatpickr;
        if (txDatePicker) {
            txDatePicker.setDate(new Date(), true);
        }
        document.getElementById('tx-type').value = 'expense';
        toggleTransferFields('tx');
        openModal('modalTransaction');
    }

    function openTransactionEditModal(btn) {
        const id = btn.getAttribute('data-id');
        const type = btn.getAttribute('data-type');
        const account_id = btn.getAttribute('data-account_id');
        const destination_account_id = btn.getAttribute('data-destination_account_id');
        const amount = btn.getAttribute('data-amount');
        const destination_amount = btn.getAttribute('data-destination_amount');
        const category_id = btn.getAttribute('data-category_id');
        const transaction_date = btn.getAttribute('data-transaction_date');
        const description = btn.getAttribute('data-description');

        document.getElementById('edit-tx-type').value = type;
        document.getElementById('edit-tx-account').value = account_id;
        document.getElementById('edit-tx-destination').value = destination_account_id;
        document.getElementById('edit-tx-amount').value = amount;
        document.getElementById('edit-tx-dest-amount').value = destination_amount;
        document.getElementById('edit-tx-category').value = category_id;
        // Set date via Flatpickr API
        const editDatePicker = document.getElementById('edit-tx-date')._flatpickr;
        if (editDatePicker) {
            editDatePicker.setDate(transaction_date, true);
        }
        document.getElementById('edit-tx-description').value = description;

        toggleTransferFields('edit-tx');

        const form = document.getElementById('form-transaction-edit');
        form.action = `/transactions/${id}`;

        openModal('modalTransactionEdit');
    }
</script>
@endsection
