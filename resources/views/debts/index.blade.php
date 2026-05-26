@extends('layouts.app')

@section('title', 'SakuPremium - Pelacak Hutang & Piutang')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- HEADER -->
    <header class="glass-panel" style="padding: 1.25rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div class="logo-container">
            <div class="logo-icon" style="background: linear-gradient(135deg, var(--accent), #f43f5e);">
                <i data-lucide="users" style="color: white; width: 24px; height: 24px;"></i>
            </div>
            <div class="logo-text">Pelacak Hutang & Piutang</div>
        </div>

        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <button class="btn btn-secondary" onclick="openModal('modalContact')">
                <i data-lucide="user-plus"></i> Kontak Baru
            </button>
            <button class="btn btn-primary" onclick="openModal('modalDebt')" style="background: linear-gradient(135deg, var(--accent), #e11d48); box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3);">
                <i data-lucide="plus-circle"></i> Catat Baru
            </button>
        </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <div class="grid-12">
        <!-- LEFT: DEBTS LIST (8 Cols) -->
        <div class="span-8">
            <section class="glass-panel" style="padding: 2rem; min-height: 500px;">
                <div class="tab-container">
                    <button class="tab-btn active" onclick="switchDebtTab(event, 'pane-hutang')">
                        <i data-lucide="arrow-down-left" style="width: 16px; height: 16px; display:inline; vertical-align:middle; margin-right: 4px;"></i>
                        Hutang Kita (Uang Masuk di Awal)
                    </button>
                    <button class="tab-btn" onclick="switchDebtTab(event, 'pane-piutang')">
                        <i data-lucide="arrow-up-right" style="width: 16px; height: 16px; display:inline; vertical-align:middle; margin-right: 4px;"></i>
                        Piutang Kita (Uang Keluar di Awal)
                    </button>
                </div>

                <!-- TAB: HUTANG -->
                <div id="pane-hutang" class="tab-pane active">
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        @forelse($debts->where('type', 'debt') as $d)
                            @php
                                $paid = (float)($d->amount - $d->remaining_amount);
                                $percent = $d->amount > 0 ? round(($paid / $d->amount) * 100) : 0;
                            @endphp
                            <div class="glass-card" style="padding: 1.5rem; position: relative; border-left: 4px solid var(--accent);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                                    <div>
                                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">{{ $d->contact->name }}</h3>
                                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                            Rekening: <strong>{{ $d->account->name }}</strong>
                                        </p>
                                        @if($d->due_date)
                                            <p style="font-size: 0.75rem; color: #fca5a5; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                                <i data-lucide="calendar-days" style="width: 12px; height: 12px;"></i>
                                                Jatuh Tempo: {{ $d->due_date->format('d M Y') }}
                                            </p>
                                        @endif
                                        @if($d->description)
                                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 8px; font-style: italic;">
                                                "{{ $d->description }}"
                                            </p>
                                        @endif
                                    </div>

                                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                                        <span class="item-amount debt" style="font-size: 1.35rem; font-weight: 700;">
                                            {{ $d->account->currency->symbol }} {{ number_format($d->remaining_amount, 2, ',', '.') }}
                                        </span>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                                            Sisa dari {{ $d->account->currency->symbol }} {{ number_format($d->amount, 2, ',', '.') }}
                                        </span>
                                        <span class="badge" style="background: {{ $d->status === 'paid_off' ? 'rgba(16,185,129,0.1)' : 'rgba(244,63,94,0.1)' }}; color: {{ $d->status === 'paid_off' ? '#a7f3d0' : '#fca5a5' }}; font-size: 0.7rem; padding: 2px 8px; margin-top: 4px;">
                                            {{ $d->status === 'paid_off' ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Progress Bar Repayment -->
                                <div style="margin-top: 1.25rem;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.35rem;">
                                        <span>Progress Pelunasan</span>
                                        <span>{{ $percent }}% Terbayar</span>
                                    </div>
                                    <div style="height: 6px; border-radius: 999px; background: rgba(255,255,255,0.05); overflow: hidden;">
                                        <div style="height: 100%; width: {{ $percent }}%; background: linear-gradient(90deg, var(--accent), #f43f5e); border-radius: 999px;"></div>
                                    </div>
                                </div>

                                <!-- CARD ACTIONS -->
                                <div style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.75rem;"
                                            onclick="openDebtEditModal(this)"
                                            data-id="{{ $d->id }}"
                                            data-contact_id="{{ $d->contact_id }}"
                                            data-account_id="{{ $d->account_id }}"
                                            data-type="{{ $d->type }}"
                                            data-amount="{{ $d->amount }}"
                                            data-due_date="{{ $d->due_date?->format('Y-m-d') ?? '' }}"
                                            data-description="{{ htmlspecialchars($d->description ?? '', ENT_QUOTES) }}">
                                            <i data-lucide="edit-3" style="width: 12px; height: 12px; display:inline;"></i> Edit
                                        </button>

                                        <form action="{{ route('debts.destroy', $d->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan hutang ini beserta seluruh riwayat cicilannya? Dampak kas flow di saldo rekening akan dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" style="padding: 5px 10px; font-size: 0.75rem;">
                                                <i data-lucide="trash-2" style="width: 12px; height: 12px; display:inline;"></i> Hapus
                                            </button>
                                        </form>
                                    </div>

                                    @if($d->status !== 'paid_off')
                                        <button class="btn btn-success" style="padding: 5px 10px; font-size: 0.75rem;" onclick="openRepaymentModal({{ $d->id }}, '{{ $d->contact->name }}', {{ $d->remaining_amount }}, {{ $d->account_id }})">
                                            <i data-lucide="coins" style="width: 12px; height: 12px; display:inline;"></i> Bayar Cicilan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
                                <i data-lucide="info" style="width: 36px; height: 36px; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                <p>Tidak ada catatan hutang pending saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- TAB: PIUTANG -->
                <div id="pane-piutang" class="tab-pane">
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        @forelse($debts->where('type', 'receivable') as $d)
                            @php
                                $paid = (float)($d->amount - $d->remaining_amount);
                                $percent = $d->amount > 0 ? round(($paid / $d->amount) * 100) : 0;
                            @endphp
                            <div class="glass-card" style="padding: 1.5rem; position: relative; border-left: 4px solid var(--success);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                                    <div>
                                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">{{ $d->contact->name }}</h3>
                                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                            Rekening: <strong>{{ $d->account->name }}</strong>
                                        </p>
                                        @if($d->due_date)
                                            <p style="font-size: 0.75rem; color: #a7f3d0; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                                <i data-lucide="calendar-days" style="width: 12px; height: 12px;"></i>
                                                Jatuh Tempo: {{ $d->due_date->format('d M Y') }}
                                            </p>
                                        @endif
                                        @if($d->description)
                                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 8px; font-style: italic;">
                                                "{{ $d->description }}"
                                            </p>
                                        @endif
                                    </div>

                                    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                                        <span class="item-amount receivable" style="font-size: 1.35rem; font-weight: 700;">
                                            {{ $d->account->currency->symbol }} {{ number_format($d->remaining_amount, 2, ',', '.') }}
                                        </span>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                                            Sisa dari {{ $d->account->currency->symbol }} {{ number_format($d->amount, 2, ',', '.') }}
                                        </span>
                                        <span class="badge" style="background: {{ $d->status === 'paid_off' ? 'rgba(16,185,129,0.1)' : 'rgba(16,185,129,0.1)' }}; color: {{ $d->status === 'paid_off' ? '#a7f3d0' : '#a7f3d0' }}; font-size: 0.7rem; padding: 2px 8px; margin-top: 4px;">
                                            {{ $d->status === 'paid_off' ? 'Lunas' : 'Belum Lunas' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Progress Bar Repayment -->
                                <div style="margin-top: 1.25rem;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.35rem;">
                                        <span>Progress Pengembalian</span>
                                        <span>{{ $percent }}% Diterima</span>
                                    </div>
                                    <div style="height: 6px; border-radius: 999px; background: rgba(255,255,255,0.05); overflow: hidden;">
                                        <div style="height: 100%; width: {{ $percent }}%; background: linear-gradient(90deg, var(--success), #34d399); border-radius: 999px;"></div>
                                    </div>
                                </div>

                                <!-- CARD ACTIONS -->
                                <div style="margin-top: 1.25rem; padding-top: 0.85rem; border-top: 1px solid rgba(255,255,255,0.04); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.75rem;"
                                            onclick="openDebtEditModal(this)"
                                            data-id="{{ $d->id }}"
                                            data-contact_id="{{ $d->contact_id }}"
                                            data-account_id="{{ $d->account_id }}"
                                            data-type="{{ $d->type }}"
                                            data-amount="{{ $d->amount }}"
                                            data-due_date="{{ $d->due_date?->format('Y-m-d') ?? '' }}"
                                            data-description="{{ htmlspecialchars($d->description ?? '', ENT_QUOTES) }}">
                                            <i data-lucide="edit-3" style="width: 12px; height: 12px; display:inline;"></i> Edit
                                        </button>

                                        <form action="{{ route('debts.destroy', $d->id) }}" method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan piutang ini beserta seluruh riwayat cicilannya? Dampak kas flow di saldo rekening akan dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" style="padding: 5px 10px; font-size: 0.75rem;">
                                                <i data-lucide="trash-2" style="width: 12px; height: 12px; display:inline;"></i> Hapus
                                            </button>
                                        </form>
                                    </div>

                                    @if($d->status !== 'paid_off')
                                        <button class="btn btn-success" style="padding: 5px 10px; font-size: 0.75rem;" onclick="openRepaymentModal({{ $d->id }}, '{{ $d->contact->name }}', {{ $d->remaining_amount }}, {{ $d->account_id }})">
                                            <i data-lucide="arrow-down-left" style="width: 12px; height: 12px; display:inline;"></i> Terima Pembayaran
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); padding: 3rem 0;">
                                <i data-lucide="info" style="width: 36px; height: 36px; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                <p>Tidak ada catatan piutang pending saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <!-- RIGHT: CONTACTS & HISTORIES (4 Cols) -->
        <div class="span-4" style="display: flex; flex-direction: column; gap: 2rem;">
            <!-- CONTACTS PANEL -->
            <section class="glass-panel" style="padding: 2rem;">
                <div class="section-title">
                    <span>Kontak Terdaftar</span>
                    <i data-lucide="contact" style="color: var(--text-secondary);"></i>
                </div>

                <div class="list-cards" style="max-height: 250px;">
                    @forelse($contacts as $c)
                        <div class="list-item-card" style="padding: 0.75rem 1rem;">
                            <div class="item-left">
                                <span class="item-title" style="font-size: 0.85rem;">{{ $c->name }}</span>
                                <span class="item-subtitle" style="font-size: 0.7rem;">{{ $c->phone ?? 'Tidak ada nomor telepon' }}</span>
                            </div>
                            <i data-lucide="user" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 1rem 0; font-size: 0.8rem;">
                            Belum ada kontak. Klik tombol Kontak Baru di atas.
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- REPAYMENTS HISTORIES -->
            <section class="glass-panel" style="padding: 2rem;">
                <div class="section-title">
                    <span>Riwayat Pembayaran Terbaru</span>
                    <i data-lucide="history" style="color: var(--text-secondary);"></i>
                </div>

                <div class="list-cards" style="max-height: 300px;">
                    @php
                        $allRepayments = collect();
                        foreach($debts as $d) {
                            foreach($d->repayments as $rep) {
                                $allRepayments->push([
                                    'repayment' => $rep,
                                    'debt' => $d
                                ]);
                            }
                        }
                        $sortedRepayments = $allRepayments->sortByDesc(fn($x) => $x['repayment']->created_at)->take(6);
                    @endphp

                    @forelse($sortedRepayments as $item)
                        <div class="list-item-card" style="padding: 0.75rem 1rem;">
                            <div class="item-left">
                                <span class="item-title" style="font-size: 0.85rem;">Cicilan: {{ $item['debt']->contact->name }}</span>
                                <span class="item-subtitle" style="font-size: 0.7rem;">
                                    Tipe: {{ $item['debt']->type === 'debt' ? 'Bayar Hutang' : 'Tarik Piutang' }}
                                </span>
                            </div>
                            <div class="item-right">
                                <span class="item-amount" style="font-size: 0.9rem; color: {{ $item['debt']->type === 'debt' ? 'var(--accent)' : 'var(--success)' }}">
                                    {{ $item['debt']->account->currency->symbol }} {{ number_format($item['repayment']->amount, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-muted); padding: 1.5rem 0; font-size: 0.8rem;">
                            Belum ada catatan cicilan pembayaran.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- 1. Modal Create Contact -->
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
                <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Susanto, Auntie Wang" required>
            </div>
            <div class="form-group">
                <label class="form-label">Nomor Telepon / WA (Opsional)</label>
                <input type="text" name="phone" class="form-control" placeholder="Contoh: 08123456789">
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi / Catatan Kontak</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Teman Kos, Agen Taiwan">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Kontak</button>
        </form>
    </div>
</div>

<!-- 2. Modal Create Debt / Pinjaman -->
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
                    <option value="">Pilih kontak...</option>
                    @foreach ($contacts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Catatan</label>
                <select name="type" class="form-control" required>
                    <option value="debt">Hutang (Kita meminjam uang dari kontak · Kas Bertambah)</option>
                    <option value="receivable">Piutang (Kita meminjamkan uang ke kontak · Kas Berkurang)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Rekening Keuangan Terkait</label>
                <select name="account_id" class="form-control" required>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Uang Pinjaman</label>
                <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Jatuh Tempo (Opsional)</label>
                <input type="text" name="due_date" class="form-control datepicker-date" placeholder="Pilih tanggal jatuh tempo...">
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan / Alasan</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Pinjaman modal usaha, Beli tiket pesawat">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Catatan</button>
        </form>
    </div>
</div>

<!-- 3. Modal Edit Debt -->
<div id="modalDebtEdit" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalDebtEdit')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Edit Hutang / Piutang</h3>
            <button class="close-btn" onclick="closeModal('modalDebtEdit')">&times;</button>
        </div>
        <form id="form-debt-edit" action="" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Kontak Debitur/Kreditur</label>
                <select name="contact_id" id="edit-debt-contact" class="form-control" required>
                    @foreach ($contacts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nominal Pinjaman Asli</label>
                <input type="number" name="amount" id="edit-debt-amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Jatuh Tempo (Opsional)</label>
                <input type="text" name="due_date" id="edit-debt-due-date" class="form-control datepicker-date" placeholder="Pilih tanggal jatuh tempo...">
            </div>
            <div class="form-group">
                <label class="form-label">Keterangan / Alasan</label>
                <input type="text" name="description" id="edit-debt-description" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- 4. Modal Cicilan / Repayment -->
<div id="modalRepayment" class="modal-overlay" onclick="closeModalOnOverlay(event, 'modalRepayment')">
    <div class="modal-content glass-panel">
        <div class="modal-header">
            <h3 class="modal-title">Rekam Cicilan Pembayaran</h3>
            <button class="close-btn" onclick="closeModal('modalRepayment')">&times;</button>
        </div>
        <form action="{{ route('debts.repayment') }}" method="POST">
            @csrf
            <input type="hidden" name="debt_id" id="pay-debt-id">
            
            <div class="form-group">
                <label class="form-label">Pembayaran Atas Nama</label>
                <input type="text" id="pay-contact-name" class="form-control" readonly style="opacity: 0.7;">
            </div>

            <div class="form-group">
                <label class="form-label">Rekening Transaksi Pelunasan</label>
                <select name="account_id" id="pay-account-id" class="form-control" required>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->currency->symbol }} {{ number_format($acc->balance, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nominal Cicilan Pembayaran</label>
                <input type="number" name="amount" id="pay-amount" class="form-control" step="0.01" min="0.01" required>
                <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                    Sisa Hutang: <span id="pay-remaining-label" style="font-weight:700;"></span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan Tambahan (Opsional)</label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Bayar lunas, Cicilan ke-1">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; background: linear-gradient(135deg, var(--success), #059669); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                Catat Pembayaran
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function switchDebtTab(evt, paneId) {
        // Get all elements with class="tab-pane" and hide them
        const panes = document.getElementsByClassName("tab-pane");
        for (let i = 0; i < panes.length; i++) {
            panes[i].classList.remove("active");
        }

        // Get all elements with class="tab-btn" and remove the class "active"
        const btns = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < btns.length; i++) {
            btns[i].classList.remove("active");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(paneId).classList.add("active");
        evt.currentTarget.classList.add("active");
    }

    function openRepaymentModal(debtId, contactName, remainingAmount, defaultAccountId) {
        document.getElementById('pay-debt-id').value = debtId;
        document.getElementById('pay-contact-name').value = contactName;
        document.getElementById('pay-amount').value = remainingAmount;
        document.getElementById('pay-amount').max = remainingAmount;
        document.getElementById('pay-account-id').value = defaultAccountId;
        document.getElementById('pay-remaining-label').innerText = remainingAmount.toLocaleString('id-ID');

        openModal('modalRepayment');
    }

    function openDebtEditModal(btn) {
        const id = btn.getAttribute('data-id');
        const contact_id = btn.getAttribute('data-contact_id');
        const amount = btn.getAttribute('data-amount');
        const due_date = btn.getAttribute('data-due_date');
        const description = btn.getAttribute('data-description');

        document.getElementById('edit-debt-contact').value = contact_id;
        document.getElementById('edit-debt-amount').value = amount;
        // Set date via Flatpickr API
        const dueDatePicker = document.getElementById('edit-debt-due-date')._flatpickr;
        if (dueDatePicker) {
            dueDatePicker.setDate(due_date || null, true);
        }
        document.getElementById('edit-debt-description').value = description;

        const form = document.getElementById('form-debt-edit');
        form.action = `/debts/${id}`;

        openModal('modalDebtEdit');
    }
</script>
@endsection
