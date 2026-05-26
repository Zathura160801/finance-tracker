@extends('layouts.app')

@section('title', 'SakuPremium - Kelola Rekening & Dompet Keuangan')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- HEADER -->
    <header class="glass-panel" style="padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="logo-container">
            <div class="logo-icon">
                <i data-lucide="credit-card" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div class="logo-text">Kelola Rekening & Dompet</div>
        </div>

        <button class="btn btn-primary" onclick="openModal('modalAccount')">
            <i data-lucide="plus-circle"></i> Tambah Akun Baru
        </button>
    </header>

    <!-- GRID OF ACCOUNTS -->
    <section class="glass-panel" style="padding: 2rem;">
        <div class="section-title">
            <span>Daftar Rekening & Dompet Aktif</span>
            <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">Total: {{ $accounts->count() }} Akun</span>
        </div>

        <div class="accounts-grid">
            @forelse ($accounts as $acc)
                <div class="glass-card account-pill {{ $acc->type }}" style="padding-bottom: 4.5rem;">
                    <div class="account-name">
                        {{ $acc->name }}
                        <span class="currency-badge">{{ $acc->currency_code }}</span>
                    </div>
                    <div class="account-number">
                        @if($acc->type === 'cash')
                            <span style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="coins" style="width:12px; height:12px;"></i> Tunai (Cash)</span>
                        @elseif($acc->type === 'bank')
                            <span style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="landmark" style="width:12px; height:12px;"></i> Rek: {{ $acc->account_number }}</span>
                        @elseif($acc->type === 'card')
                            <span style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="contactless" style="width:12px; height:12px;"></i> No: {{ $acc->account_number }}</span>
                        @elseif($acc->type === 'e_wallet')
                            <span style="display:inline-flex; align-items:center; gap:4px;"><i data-lucide="smartphone" style="width:12px; height:12px;"></i> Tlp: {{ $acc->account_number }}</span>
                        @else
                            {{ $acc->account_number ?? 'Uang Deposit' }}
                        @endif
                    </div>
                    <div class="account-balance">
                        <span style="font-size: 1rem; color: var(--text-secondary); font-weight: 500;">
                            {{ $acc->currency->symbol }}
                        </span>
                        {{ number_format($acc->balance, 2, ',', '.') }}
                    </div>

                    <!-- ACTION FOOTER ON CARD -->
                    <div style="position: absolute; bottom: 12px; left: 20px; right: 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.04); padding-top: 10px;">
                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.75rem; border-radius: 6px; display: flex; align-items: center; gap: 4px;"
                            onclick="openAccountEditModal(this)" 
                            data-id="{{ $acc->id }}"
                            data-name="{{ htmlspecialchars($acc->name, ENT_QUOTES) }}"
                            data-account_number="{{ $acc->account_number ?? '' }}"
                            data-type="{{ $acc->type }}"
                            data-currency_code="{{ $acc->currency_code }}"
                            data-balance="{{ $acc->balance }}">
                            <i data-lucide="edit-3" style="width: 12px; height: 12px;"></i> Edit
                        </button>

                        <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini? Akun hanya dapat dihapus jika belum memiliki riwayat transaksi/relasi.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" style="padding: 5px 10px; font-size: 0.75rem; border-radius: 6px; display: flex; align-items: center; gap: 4px;">
                                <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem 0;">
                    <i data-lucide="wallet" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.3;"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">Belum ada rekening terdaftar</p>
                    <p style="font-size: 0.85rem; margin-top: 4px;">Silakan buat rekening pertama Anda dengan klik tombol Tambah Akun Baru di atas.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

<!-- MODALS -->

<!-- 1. Modal Create Account -->
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
                <input type="text" name="name" class="form-control" placeholder="Contoh: Bank BCA, Gopay" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Rekening / Rekening Koran / Kartu (Opsional)</label>
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
                    @foreach ($currencies as $c)
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

<!-- 2. Modal Edit Account -->
<div id="modalAccountEdit" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalAccountEdit')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Edit Rekening Keuangan</h3>
            <button class="close-btn" onclick="closeModal('modalAccountEdit')">&times;</button>
        </div>
        <form id="form-account-edit" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Akun/Rekening</label>
                <input type="text" name="name" id="edit-account-name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Rekening / Kartu (Opsional)</label>
                <input type="text" name="account_number" id="edit-account-number" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Akun</label>
                <select name="type" id="edit-account-type" class="form-control" required>
                    <option value="bank">Bank / Rekening Koran</option>
                    <option value="cash">Uang Tunai (Cash)</option>
                    <option value="card">Kartu Prabayar (EasyCard, dll)</option>
                    <option value="e_wallet">Dompet Digital (GoPay, LinePay, dll)</option>
                    <option value="deposit">Deposit Jaminan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Mata Uang</label>
                <select name="currency_code" id="edit-account-currency" class="form-control" required>
                    @foreach ($currencies as $c)
                        <option value="{{ $c->code }}">{{ $c->code }} - {{ $c->name }} ({{ $c->symbol }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Saldo Saat Ini (Penyesuaian)</label>
                <input type="number" name="balance" id="edit-account-balance" class="form-control" step="0.01" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openAccountEditModal(btn) {
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-name');
        const account_number = btn.getAttribute('data-account_number');
        const type = btn.getAttribute('data-type');
        const currency_code = btn.getAttribute('data-currency_code');
        const balance = btn.getAttribute('data-balance');

        document.getElementById('edit-account-name').value = name;
        document.getElementById('edit-account-number').value = account_number;
        document.getElementById('edit-account-type').value = type;
        document.getElementById('edit-account-currency').value = currency_code;
        document.getElementById('edit-account-balance').value = balance;

        const form = document.getElementById('form-account-edit');
        form.action = `/accounts/${id}`;

        openModal('modalAccountEdit');
    }
</script>
@endsection
