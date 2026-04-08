@extends('layouts.app')

@section('content_title', 'Detail Hutang: ' . $supplier->nama)

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center">{{ $supplier->nama }}</h3>
                <p class="text-muted text-center">{{ $supplier->alamat ?? '-' }}</p>
                <div class="text-center mb-3">
                     <span class="badge badge-danger p-2 d-inline-block" style="font-size: 1.1rem; white-space: normal; line-height: 1.5;">
                         Total Sisa Hutang: Rp {{ number_format($purchases->sum('remaining_debt'), 0, ',', '.') }}
                     </span>
                </div>
                <a href="{{ route('payable.index') }}" class="btn btn-default btn-block"><b><i class="fas fa-arrow-left"></i> Kembali</b></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Riwayat Transaksi (Lunas & Hutang)</h3>
                <a href="{{ route('payable.print-all', $supplier->id) }}" target="_blank" class="btn btn-primary btn-sm ml-auto">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No Faktur</th>
                            <th>Tanggal</th>
                            <th>Total Tagihan</th>
                            <th>Sudah Bayar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $trx)
                        <tr>
                            <td>{{ $trx->no_faktur }}</td>
                            <td>{{ date('d-m-Y', strtotime($trx->tanggal)) }}</td>
                            <td>Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($trx->bayar, 0, ',', '.') }}</td>
                            <td>
                                @if($trx->remaining_debt > 0)
                                    <span class="badge badge-danger">Belum Lunas</span><br>
                                    <small class="text-danger font-weight-bold">Sisa: Rp {{ number_format($trx->remaining_debt, 0, ',', '.') }}</small>
                                @else
                                    <span class="badge badge-success">Lunas</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" onclick="showHistory({{ $trx->id }}, '{{ $trx->no_faktur }}')">
                                    <i class="fas fa-history"></i> Riwayat
                                </button>
                                <a href="{{ route('payable.payment.print', $trx->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Kartu Hutang">
                                    <i class="fas fa-print"></i> Kartu
                                </a>
                                @if($trx->remaining_debt > 0)
                                <button type="button" class="btn btn-success btn-sm" onclick="showPayModal({{ $trx->id }}, {{ $trx->remaining_debt }}, '{{ $trx->no_faktur }}')">
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('payable.payment.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-money-check-alt mr-2"></i> Pembayaran Hutang</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="purchase_id" id="payPurchaseId">
                    
                    <div class="bg-light p-3 rounded mb-4 border-left border-success" style="border-left-width: 4px !important;">
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted uppercase font-weight-bold d-block">No. Faktur</small>
                                <span id="payNoTrx" class="font-weight-bold text-dark">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-muted small uppercase font-weight-bold">Sisa Hutang Saat Ini</label>
                        <input type="text" class="form-control font-weight-bold text-danger" style="font-size: 1.2rem; background: transparent; border: none; padding: 0;" id="payRemaining" disabled>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small uppercase font-weight-bold">Tanggal & Jam Bayar</label>
                                <input type="datetime-local" name="payment_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label class="text-muted small uppercase font-weight-bold">Jumlah Bayar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 font-weight-bold">Rp</span>
                                    </div>
                                    <input type="text" name="amount" class="form-control border-left-0 font-weight-bold currency-input" id="payAmount" required placeholder="0" inputmode="numeric">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-muted small uppercase font-weight-bold">Catatan (Opsional)</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Contoh: Transfer M-Banking..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 justify-content-between">
                    <button type="button" class="btn btn-default font-weight-bold px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> SIMPAN PEMBAYARAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal History -->
<div class="modal fade" id="modalHistory" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-history mr-2"></i> Riwayat Pembayaran: <span id="histNoTrx"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-top-0 py-3 pl-4">Tanggal Pembayaran</th>
                                <th class="border-top-0 py-3 text-right">Nominal</th>
                                <th class="border-top-0 py-3 text-center">Petugas</th>
                                <th class="border-top-0 py-3 pr-4">Catatan</th>
                                <th class="border-top-0 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="histBody">
                            <!-- Loaded via Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<style>
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .uppercase { text-transform: uppercase; }
    .currency-input { text-align: right; font-family: monospace; }
</style>
<script>
    function showPayModal(id, remaining, noTrx) {
        $('#payPurchaseId').val(id);
        $('#payNoTrx').text(noTrx);
        $('#payRemaining').val(formatRupiah(remaining));
        $('#payAmount').val('');
        $('#payAmount').data('max', remaining); // Store raw limit
        $('#modalPay').modal('show');
    }

    function showHistory(id, noTrx) {
        $('#histNoTrx').text(noTrx);
        $('#histBody').html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...</td></tr>');
        $('#modalHistory').modal('show');

        // Fetch History
        $.get("{{ url('payable/payment') }}/" + id + "/history", function(data) {
            let html = '';
            if(data.length === 0) {
                html = '<tr><td colspan="5" class="text-center text-muted py-4 italic">Belum ada riwayat pembayaran untuk faktur ini.</td></tr>';
            } else {
                data.forEach(item => {
                    // Create Date object
                    let d = new Date(item.payment_date);
                    // Format explicitly 
                    let dateStr = [('0'+d.getDate()).slice(-2), ('0'+(d.getMonth()+1)).slice(-2), d.getFullYear()].join('-') + ' ' +
                                  [('0'+d.getHours()).slice(-2), ('0'+d.getMinutes()).slice(-2)].join(':');
                                  
                    let user = item.user ? item.user.name : '-';
                    html += `
                        <tr>
                            <td class="pl-4 align-middle font-weight-bold">${dateStr}</td>
                            <td class="text-right align-middle text-success font-weight-bold">${formatRupiah(item.amount)}</td>
                            <td class="text-center align-middle"><span class="badge badge-light p-2">${user}</span></td>
                            <td class="pr-4 align-middle text-muted small">${item.note || '-'}</td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="cancelPayment(${item.id})" title="Batal Bayar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#histBody').html(html);
        });
    }

    function cancelPayment(paymentId) {
        if (!confirm('Apakah Anda yakin ingin membatalkan pembayaran ini? Nominal akan dikembalikan ke sisa hutang.')) {
            return;
        }

        $.ajax({
            url: "{{ url('payable/payment') }}/" + paymentId,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Terjadi kesalahan saat membatalkan pembayaran.');
            }
        });
    }

    function formatRupiah(angka) {
         return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
    }

    // Currency Input Formatter & Max Limit
    $(document).on('keyup', '.currency-input', function(e) {
        let valRaw = $(this).val().replace(/\./g, '');
        if (valRaw === '') {
            $(this).val('');
            return;
        }
        if(isNaN(valRaw)) { 
            return; // ignore non numeric
        }
        
        let n = parseInt(valRaw, 10);
        
        // Check Max Limit
        let max = $(this).data('max');
        if (max !== undefined && n > max) {
            n = max; // Cap at max
        }
        
        // Format with dots
        let formatted = new Intl.NumberFormat('id-ID').format(n);
        $(this).val(formatted);
    });

    // Clean currency before submit
    $('form').on('submit', function() {
        $('.currency-input').each(function() {
            let val = $(this).val().replace(/\./g, '');
            $(this).val(val);
        });
    });
</script>
@endsection

@endsection
