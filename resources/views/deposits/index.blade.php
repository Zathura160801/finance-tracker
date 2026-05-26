@extends('layouts.app')

@section('title', 'SakuPremium - Pelacak Deposit Jaminan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- HEADER -->
    <header class="glass-panel" style="padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="logo-container">
            <div class="logo-icon" style="background: linear-gradient(135deg, #eab308, #ca8a04);">
                <i data-lucide="shield-check" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div class="logo-text">Pelacak Deposit Jaminan</div>
        </div>

        <button class="btn btn-primary" onclick="openModal('modalDeposit')" style="background: linear-gradient(135deg, #eab308, #d97706); box-shadow: 0 4px 15px rgba(234, 179, 8, 0.3);">
            <i data-lucide="plus-circle"></i> Catat Deposit Baru
        </button>
    </header>

    <!-- CONTENT WRAPPER -->
    <div class="grid-12">
        <!-- LEFT: ACTIVE DEPOSITS (8 Cols) -->
        <div class="span-8">
            <section class="glass-panel" style="padding: 2rem; min-height: 500px;">
                <div class="section-title">
                    <span>Deposit Jaminan yang Sedang Aktif</span>
                    <span style="font-size: 0.8rem; color: #fef08a; font-weight: 500; background: rgba(234,179,8,0.1); padding: 2px 8px; border-radius: 20px;">
                        {{ $deposits->where('status', 'active')->count() }} Aktif
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    @forelse($deposits->where('status', 'active') as $dep)
                        <div class="glass-card" style="padding: 1.5rem; position: relative; border-left: 4px solid #eab308;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                                <div>
                                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">{{ $dep->name }}</h3>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        Dibayar melalui Rekening: <strong>{{ $dep->account->name }}</strong>
                                    </p>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        Tanggal Bayar: <strong>{{ $dep->created_at->format('d M Y') }}</strong>
                                    </p>
                                    @if($dep->description)
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 8px; font-style: italic;">
                                            "{{ $dep->description }}"
                                        </p>
                                    @endif
                                </div>

                                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                                    <span class="item-amount" style="color: #fef08a; font-size: 1.35rem; font-weight: 700;">
                                        {{ $dep->account->currency->symbol }} {{ number_format($dep->amount, 2, ',', '.') }}
                                    </span>
                                    <span class="badge" style="background: rgba(234, 179, 8, 0.1); color: #fef08a; font-size: 0.7rem; padding: 2px 8px;">
                                        Aktif / Belum Kembali
                                    </span>
                                </div>
                            </div>

                            <!-- CARD ACTIONS -->
                            <div style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.75rem;"
                                        onclick="openDepositEditModal(this)"
                                        data-id="{{ $dep->id }}"
                                        data-name="{{ htmlspecialchars($dep->name, ENT_QUOTES) }}"
                                        data-account_id="{{ $dep->account_id }}"
                                        data-amount="{{ $dep->amount }}"
                                        data-description="{{ htmlspecialchars($dep->description ?? '', ENT_QUOTES) }}">
                                        <i data-lucide="edit-3" style="width: 12px; height: 12px; display:inline;"></i> Edit
                                    </button>

                                    <form action="{{ route('deposits.destroy', $dep->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan deposit ini? Dampak pengeluaran uang deposit di saldo rekening Anda akan dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" style="padding: 5px 10px; font-size: 0.75rem;">
                                            <i data-lucide="trash-2" style="width: 12px; height: 12px; display:inline;"></i> Hapus
                                        </button>
                                    </form>
                                </div>

                                <button class="btn btn-success" style="padding: 5px 10px; font-size: 0.75rem;" onclick="openReturnModal({{ $dep->id }}, '{{ $dep->name }}', {{ $dep->amount }}, {{ $dep->account_id }})">
                                    <i data-lucide="check-circle" style="width: 12px; height: 12px; display:inline;"></i> Kembalikan Uang Deposit
                                </button>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 4rem 0;">
                            <i data-lucide="info" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p style="font-size: 1.05rem; font-weight: 500;">Tidak ada deposit aktif</p>
                            <p style="font-size: 0.85rem; margin-top: 4px;">Uang deposit jaminan yang sedang dititipkan di pihak lain akan tercatat di sini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- RIGHT: RETURNED DEPOSITS HISTORIES (4 Cols) -->
        <div class="span-4">
            <section class="glass-panel" style="padding: 2rem; min-height: 500px; display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="section-title" style="margin: 0;">
                    <span>Riwayat Pengembalian</span>
                    <i data-lucide="history" style="color: var(--text-secondary);"></i>
                </div>

                <div class="list-cards" style="max-height: 420px; overflow-y: auto;">
                    @forelse($deposits->where('status', 'returned') as $dep)
                        <div class="list-item-card" style="padding: 1rem; flex-direction: column; align-items: flex-start; gap: 6px;">
                            <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                                <span class="item-title" style="font-size: 0.85rem; font-weight: 600;">{{ $dep->name }}</span>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #34d399; font-size: 0.6rem; padding: 2px 6px;">Returned</span>
                            </div>
                            <span class="item-amount" style="font-size: 0.95rem; color: #a7f3d0; font-weight: 700;">
                                {{ $dep->account->currency->symbol }} {{ number_format($dep->amount, 2, ',', '.') }}
                            </span>
                            <span class="item-subtitle" style="font-size: 0.7rem; color: var(--text-muted);">
                                Dikembalikan ke: {{ $dep->account->name }}
                            </span>
                            @if($dep->description)
                                <span class="item-subtitle" style="font-size: 0.7rem; font-style: italic; color: var(--text-secondary); margin-top: 2px; border-left: 2px solid rgba(255,255,255,0.08); padding-left: 6px;">
                                    {{ $dep->description }}
                                </span>
                            @endif
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 2rem 0; font-size: 0.85rem;">
                            Belum ada riwayat pengembalian deposit.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- 1. Modal Create Deposit -->
<div id="modalDeposit" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDeposit')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Catat Pembayaran Deposit Jaminan</h3>
            <button class="close-btn" onclick="closeModal('modalDeposit')">&times;</button>
        </div>
        <form action="{{ route('deposits.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama/Keperluan Uang Jaminan</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Jaminan sewa apartemen, Deposit air/listrik" required>
            </div>
            <div class="form-group">
                <label class="form-label">Rekening Sumber Pembayaran (Uang Keluar)</label>
                <select name="account_id" class="form-control" required>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Uang Jaminan (Deposit)</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label class="form-label">Catatan Tambahan (Keterangan)</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Akan dikembalikan 6 bulan lagi">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Catatan</button>
        </form>
    </div>
</div>

<!-- 2. Modal Edit Deposit -->
<div id="modalDepositEdit" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDepositEdit')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Edit Detail Deposit</h3>
            <button class="close-btn" onclick="closeModal('modalDepositEdit')">&times;</button>
        </div>
        <form id="form-deposit-edit" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama/Keperluan Deposit</label>
                <input type="text" name="name" id="edit-deposit-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Uang Jaminan (Deposit)</label>
                <input type="number" name="amount" id="edit-deposit-amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan Lainnya</label>
                <input type="text" name="description" id="edit-deposit-description" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- 3. Modal Return Deposit -->
<div id="modalDepositReturn" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDepositReturn')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Proses Pengembalian Deposit</h3>
            <button class="close-btn" onclick="closeModal('modalDepositReturn')">&times;</button>
        </div>
        <form action="{{ route('deposits.return') }}" method="POST">
            @csrf
            <input type="hidden" name="deposit_id" id="return-deposit-id">
            
            <div class="form-group">
                <label class="form-label">Nama Item Jaminan</label>
                <input type="text" id="return-deposit-name" class="form-control" readonly style="opacity: 0.7;">
            </div>

            <div class="form-group">
                <label class="form-label">Rekening Penerima Pengembalian (Uang Masuk)</label>
                <select name="account_id" id="return-account-id" class="form-control" required>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nominal Pengembalian</label>
                <input type="text" id="return-amount-label" class="form-control" readonly style="opacity: 0.7;">
            </div>

            <div class="form-group">
                <label class="form-label">Catatan Pengembalian (Opsional)</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Dikembalikan tunai, Dipotong biaya admin Rp 50.000">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; background: linear-gradient(135deg, var(--success), #059669); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                Catat Pengembalian Uang
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openReturnModal(depositId, depositName, amount, defaultAccountId) {
        document.getElementById('return-deposit-id').value = depositId;
        document.getElementById('return-deposit-name').value = depositName;
        document.getElementById('return-amount-label').value = amount.toLocaleString('id-ID');
        document.getElementById('return-account-id').value = defaultAccountId;

        openModal('modalDepositReturn');
    }

    function openDepositEditModal(btn) {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const amount = btn.getAttribute('data-amount');
        const description = btn.getAttribute('data-description');

        document.getElementById('edit-deposit-name').value = name;
        document.getElementById('edit-deposit-amount').value = amount;
        document.getElementById('edit-deposit-description').value = description;

        const form = document.getElementById('form-deposit-edit');
        form.action = `/deposits/${id}`;

        openModal('modalDepositEdit');
    }
</script>
@endsection
