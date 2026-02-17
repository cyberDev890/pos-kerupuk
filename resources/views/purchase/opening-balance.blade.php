@extends('layouts.app')
@section('content_title', 'Saldo Awal Hutang Suplier')
@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card card-outline card-primary shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-muted">
                    <i class="fas fa-edit mr-1 text-primary"></i> Formulir Input Saldo Awal
                </h3>
            </div>
            <form action="{{ route('transaction.purchase.opening-balance.store') }}" method="POST" id="openingBalanceForm">
                @csrf
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="text-muted small uppercase font-weight-bold">No. Referensi / Faktur</label>
                                <input type="text" name="no_faktur" class="form-control bg-light font-weight-bold" value="{{ $no_faktur }}" readonly required>
                                <small class="text-muted">Nomor otomatis untuk pencatatan sistem.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="text-muted small uppercase font-weight-bold">Tanggal Terhutang</label>
                                <input type="date" name="tanggal" class="form-control font-weight-bold" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-muted small uppercase font-weight-bold">Pilih Suplier</label>
                        <select name="supplier_id" class="form-control select2" required>
                            <option value="">Cari Suplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-muted small uppercase font-weight-bold">Total Nominal Hutang</label>
                        <div class="input-group input-group-lg">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0 font-weight-bold text-primary">Rp</span>
                            </div>
                            <input type="text" name="total_hutang" id="total_hutang" class="form-control border-left-0 font-weight-bold currency-input text-primary" placeholder="0" required>
                        </div>
                        <small class="text-muted italic">Masukkan total sisa hutang yang masih harus dibayarkan.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-muted small uppercase font-weight-bold">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Saldo bawaan dari pembukuan manual tahun 2023..."></textarea>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-flex justify-content-between">
                    <a href="{{ route('transaction.purchase.index') }}" class="btn btn-link text-muted font-weight-bold mt-1">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-2"></i> SIMPAN SALDO AWAL
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card bg-gradient-info border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white rounded-circle p-2 mr-3 opacity-90 shadow-sm" style="width: 50px; height: 50px; display: grid; place-items: center;">
                        <i class="fas fa-lightbulb text-info fa-lg"></i>
                    </div>
                    <h5 class="mb-0 font-weight-bold text-white">Panduan Pengisian</h5>
                </div>
                <hr class="border-light opacity-25 mt-0 mb-4">
                <p class="text-white opacity-90 mb-3">
                    Fitur ini digunakan untuk mencatat <strong>hutang lama</strong> kepada suplier yang detail barang per-itemnya sudah tidak perlu dimasukkan ke aplikasi (stok lama).
                </p>
                <ul class="text-white opacity-90 pl-3">
                    <li class="mb-2">Masukkan nominal <strong>sisa hutang</strong> yang belum lunas.</li>
                    <li class="mb-2">Data ini akan otomatis muncul di menu <strong>Hutang Suplier</strong>.</li>
                    <li>Anda dapat mengelola pembayaran cicilannya melalui menu tersebut setelah data ini disimpan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <style>
        .uppercase { text-transform: uppercase; }
        .italic { font-style: italic; }
        .opacity-90 { opacity: 0.9; }
        .currency-input { text-align: right; letter-spacing: 0.5px; }
        .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
    </style>
@endsection

@section('scripts')
<script src="{{ asset('adminlte') }}/plugins/select2/js/select2.full.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Format currency on typing
        $('.currency-input').on('keyup', function() {
            let val = $(this).val().replace(/\./g, '');
            if (!isNaN(val) && val !== '') {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            }
        });

        $('#openingBalanceForm').on('submit', function() {
            // Clean currency formatting before submit
            $('.currency-input').each(function() {
                let val = $(this).val().replace(/\./g, '');
                $(this).val(val);
            });
        });
    });
</script>
@endsection
