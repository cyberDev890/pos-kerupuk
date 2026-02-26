@extends('layouts.app')

@section('content_title', 'Detail Piutang: ' . $customer->nama)

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $customer->nama }}</h3>
                <p class="text-muted text-center">{{ $customer->alamat ?? '-' }}</p>
                <div class="text-center mb-3">
                     <span class="badge badge-danger p-2" style="font-size: 1.2rem;">
                         Sisa Hutang: Rp {{ number_format($transactions->sum('remaining_debt'), 0, ',', '.') }}
                     </span>
                </div>
                <a href="{{ route('receivable.index') }}" class="btn btn-default btn-block"><b><i class="fas fa-arrow-left"></i> Kembali</b></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Riwayat Transaksi (Lunas & Piutang)</h3>
                <a href="{{ route('receivable.print-all', $customer->id) }}" target="_blank" class="btn btn-primary btn-sm ml-auto">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total Belanja</th>
                            <th>Sudah Bayar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $trx)
                        <tr>
                            <td>{{ $trx->no_transaksi }}</td>
                            <td>{{ date('d-m-Y', strtotime($trx->tanggal)) }}</td>
                            <td>Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($trx->bayar + ($trx->payments->sum('amount') ?? 0) - ($trx->bayar), 0, ',', '.') }} 
                                <small class="text-muted d-block">(Awal: {{ number_format($trx->bayar) }})</small>
                            </td>
                            <td>
                                @if($trx->remaining_debt > 0)
                                    <span class="badge badge-danger">Belum Lunas</span><br>
                                    <small class="text-danger font-weight-bold">Sisa: Rp {{ number_format($trx->remaining_debt, 0, ',', '.') }}</small>
                                @else
                                    <span class="badge badge-success">Lunas</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" onclick="printRaw({{ $trx->id }})" title="Cetak Thermal">
                                    <i class="fas fa-print"></i>
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="showHistory({{ $trx->id }}, '{{ $trx->no_transaksi }}')">
                                    <i class="fas fa-history"></i> Riwayat
                                </button>
                                <a href="{{ route('receivable.payment.print', $trx->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Kartu Piutang">
                                    <i class="fas fa-print"></i> Kartu
                                </a>
                                @if($trx->remaining_debt > 0)
                                <button type="button" class="btn btn-success btn-sm" onclick="showPayModal({{ $trx->id }}, {{ $trx->remaining_debt }}, '{{ $trx->no_transaksi }}')">
                                    <i class="fas fa-money-bill-wave"></i> Bayar
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pay -->
<div class="modal fade" id="modalPay" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('receivable.payment.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Pembayaran Hutang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="transaction_id" id="payTransactionId">
                    <div class="form-group">
                        <label>No Transaksi</label>
                        <input type="text" class="form-control" id="payNoTrx" disabled>
                    </div>
                    <div class="form-group">
                        <label>Sisa Hutang</label>
                        <input type="text" class="form-control" id="payRemaining" disabled>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal & Jam Bayar</label>
                                <input type="datetime-local" name="payment_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Jumlah Bayar (Rp)</label>
                                <input type="text" name="amount" class="form-control currency-input" id="payAmount" required placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Catatan (Opsional)</label>
                        <input type="text" name="note" class="form-control" placeholder="Contoh: Transfer Bank">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal History -->
<div class="modal fade" id="modalHistory" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Pembayaran: <span id="histNoTrx"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Penerima</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="histBody">
                            <!-- Loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function showPayModal(id, remaining, noTrx) {
        $('#payTransactionId').val(id);
        $('#payNoTrx').val(noTrx);
        $('#payRemaining').val(formatRupiah(remaining));
        $('#payAmount').val('');
        $('#payAmount').data('max', remaining); // Store raw limit
        $('#modalPay').modal('show');
    }

    function showHistory(id, noTrx) {
        $('#histNoTrx').text(noTrx);
        $('#histBody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#modalHistory').modal('show');

        // Fetch History
        $.get("{{ url('receivable/payment') }}/" + id + "/history", function(data) {
            let html = '';
            if(data.length === 0) {
                html = '<tr><td colspan="4" class="text-center text-muted">Belum ada riwayat pembayaran.</td></tr>';
            } else {
                data.forEach(item => {
                    let date = new Date(item.payment_date).toLocaleDateString('id-ID');
                    let user = item.user ? item.user.name : '-';
                    html += `
                        <tr>
                            <td>${date}</td>
                            <td class="font-weight-bold text-success">${formatRupiah(item.amount)}</td>
                            <td>${user}</td>
                            <td>${item.note || '-'}</td>
                        </tr>
                    `;
                });
            }
            $('#histBody').html(html);
        });
    }

    function printRaw(id) {
        let url = "{{ route('receivable.payment.print-raw', ':id') }}";
        url = url.replace(':id', id);
        
        smartPrint(url);
    }

    function formatRupiah(angka) {
         return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    // Currency Input Formatter & Max Limit
    $(document).on('keyup', '.currency-input', function(e) {
        let valRaw = $(this).val().replace(/\D/g, '');
        if (valRaw === '') {
            $(this).val('');
            return;
        }
        
        let n = parseInt(valRaw, 10);
        
        // Check Max Limit if exists (e.g. for Payment Amount)
        let max = $(this).data('max');
        if (max !== undefined && n > max) {
            n = max; // Cap at max
        }
        
        // Format with dots
        let formatted = n.toLocaleString('id-ID');
        $(this).val(formatted);
    });
</script>
@endsection

@endsection
