@extends('layouts.app')
@section('content_title', 'Hutang Suplier (Payables)')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-muted">
                    <i class="fas fa-file-invoice-dollar mr-1 text-primary"></i> Daftar Hutang Suplier
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table1" class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 border-top-0" style="width: 15%">No. Faktur</th>
                                <th class="py-3 border-top-0">Suplier</th>
                                <th class="py-3 border-top-0 text-center">Tanggal</th>
                                <th class="py-3 border-top-0 text-right">Total Tagihan</th>
                                <th class="py-3 border-top-0 text-right">Telah Dibayar</th>
                                <th class="py-3 border-top-0 text-right">Sisa Hutang</th>
                                <th class="py-3 border-top-0 text-center" style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payables as $payable)
                            <tr>
                                <td class="font-weight-bold text-dark">{{ $payable->no_faktur }}</td>
                                <td>
                                    <span class="d-block font-weight-bold">{{ $payable->supplier->nama }}</span>
                                    <small class="text-muted">{{ $payable->supplier->telepon ?? '-' }}</small>
                                </td>
                                <td class="text-center text-muted small">{{ date('d M Y', strtotime($payable->tanggal)) }}</td>
                                <td class="text-right">Rp {{ number_format($payable->total_harga, 0, ',', '.') }}</td>
                                <td class="text-right text-success italic">Rp {{ number_format($payable->bayar, 0, ',', '.') }}</td>
                                <td class="text-right font-weight-bold text-danger">Rp {{ number_format($payable->remaining_debt, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-success btn-pay px-3 shadow-xs" 
                                                data-id="{{ $payable->id }}" 
                                                data-no="{{ $payable->no_faktur }}"
                                                data-debt="{{ $payable->remaining_debt }}"
                                                data-supplier="{{ $payable->supplier->nama }}">
                                            <i class="fas fa-hand-holding-usd mr-1"></i> Bayar
                                        </button>
                                        <button class="btn btn-sm btn-outline-info btn-history px-3 shadow-xs" data-id="{{ $payable->id }}">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bayar -->
<div class="modal fade" id="modal-pay" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-money-check-alt mr-2"></i> Pembayaran Hutang</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('payable.payment.store') }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_id" id="pay_purchase_id">
                <div class="modal-body p-4">
                    <div class="bg-light p-3 rounded mb-4 border-left border-success" style="border-left-width: 4px !important;">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted uppercase font-weight-bold d-block">No. Faktur</small>
                                <span id="pay_no_faktur" class="font-weight-bold text-dark">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted uppercase font-weight-bold d-block">Suplier</small>
                                <span id="pay_supplier" class="font-weight-bold text-dark">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-muted small uppercase font-weight-bold">Sisa Hutang Saat Ini</label>
                        <h4 class="text-danger font-weight-bold mb-0" id="pay_remaining_debt_text">Rp 0</h4>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small uppercase font-weight-bold">Tanggal Bayar</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted small uppercase font-weight-bold">Jumlah Bayar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 font-weight-bold">Rp</span>
                                    </div>
                                    <input type="text" name="amount" id="pay_amount" class="form-control border-left-0 font-weight-bold currency-input" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-muted small uppercase font-weight-bold">Catatan (Opsional)</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Contoh: Pembayaran cicilan ke-2..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 justify-content-between">
                    <button type="button" class="btn btn-default font-weight-bold px-4" data-dismiss="modal text-muted">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> SIMPAN PEMBAYARAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Riwayat -->
<div class="modal fade" id="modal-history">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-history mr-2"></i> Riwayat Pembayaran Cicilan</h5>
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
                            </tr>
                        </thead>
                        <tbody id="history-content">
                            <!-- Items via JS -->
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
@endsection

@section('scripts')
<style>
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .italic { font-style: italic; }
    .opacity-25 { opacity: 0.25; }
    .uppercase { text-transform: uppercase; }
    .currency-input { text-align: right; font-family: monospace; }
</style>
<script>
    $(document).ready(function() {
        // Handle Bayar Button
        $('.btn-pay').click(function() {
            let id = $(this).data('id');
            let no = $(this).data('no');
            let supplier = $(this).data('supplier');
            let debt = $(this).data('debt');

            $('#pay_purchase_id').val(id);
            $('#pay_no_faktur').text(no);
            $('#pay_supplier').text(supplier);
            $('#pay_remaining_debt_text').text('Rp ' + new Intl.NumberFormat('id-ID').format(debt));
            
            // Default amount to full debt, formatted
            $('#pay_amount').val(new Intl.NumberFormat('id-ID').format(debt));
            
            $('#modal-pay').modal('show');
        });

        // Format currency on typing
        $('#pay_amount').on('keyup', function() {
            let val = $(this).val().replace(/\./g, '');
            if (!isNaN(val) && val !== '') {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            }
        });

        // Handle History Button
        $('.btn-history').click(function() {
            let id = $(this).data('id');
            $('#history-content').html('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...</td></tr>');
            $('#modal-history').modal('show');

            $.get('/payable/payment/' + id + '/history', function(data) {
                let html = '';
                if(data.length === 0) {
                    html = '<tr><td colspan="4" class="text-center py-4 text-muted italic">Belum ada riwayat pembayaran untuk faktur ini.</td></tr>';
                } else {
                    data.forEach(item => {
                        html += `<tr>
                            <td class="pl-4 align-middle font-weight-bold">${item.payment_date}</td>
                            <td class="text-right align-middle text-primary font-weight-bold">Rp ${new Intl.NumberFormat('id-ID').format(item.amount)}</td>
                            <td class="text-center align-middle"><span class="badge badge-light p-2">${item.user ? item.user.name : '-'}</span></td>
                            <td class="pr-4 align-middle text-muted small">${item.note ?? '-'}</td>
                        </tr>`;
                    });
                }
                $('#history-content').html(html);
            });
        });

        // Clean currency before submit
        $('form').on('submit', function() {
            $('.currency-input').each(function() {
                let val = $(this).val().replace(/\./g, '');
                $(this).val(val);
            });
        });
    });
</script>
@endsection
