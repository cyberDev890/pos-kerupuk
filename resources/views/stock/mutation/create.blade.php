@extends('layouts.app')

@section('content_title', 'Mutasi Stok (Gudang -> Toko)')

@section('styles')
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <style>
        .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
    </style>
@endsection

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Form Mutasi Stok</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('stock.mutation.store') }}" method="POST">
            @csrf
            
            <x-alert :errors="$errors" />
            <x-alert :type="'success'" :errors="session('success')" />
            <x-alert :type="'danger'" :errors="session('error')" />

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Pilih Produk</label>
                        <select name="product_id" id="productSelect" class="form-control select2" required>
                            <option value="">-- Cari Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                    data-stok-gudang="{{ $product->stok_gudang }}"
                                    data-stok-toko="{{ $product->stok }}"
                                    data-unit="{{ $product->unit->satuan_kecil ?? 'Pcs' }}"
                                    data-unit-besar="{{ $product->unit->satuan_besar ?? 'Bal' }}"
                                    data-isi="{{ $product->unit->isi ?? 1 }}">
                                    {{ $product->nama_produk }} (Gudang: {{ floor($product->stok_gudang / max($product->unit->isi ?? 1, 1)) }} {{ $product->unit->satuan_besar ?? 'Bal' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="infoArea" style="display: none;">
                        <div class="callout callout-info">
                            <h5>Info Stok</h5>
                            <p>
                                <strong>Gudang:</strong> <span id="infoGudang">-</span><br>
                                <strong>Toko:</strong> <span id="infoToko">-</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label>Jumlah Mutasi (Satuan Ball/Besar)</label>
                     <div class="input-group mb-3">
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                        <div class="input-group-append">
                            <span class="input-group-text big-unit-name">Ball</span>
                            <input type="hidden" name="unit_choice" value="besar">
                        </div>
                    </div>
                    <small class="text-muted" id="conversionInfo"></small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Proses Mutasi</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('adminlte') }}/plugins/select2/js/select2.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        let selectedProduct = null;

        $('#productSelect').on('select2:select', function(e) {
            let data = $(this).find(':selected').data();
            selectedProduct = data;
            
            let isi = Math.max(data.isi || 1, 1);
            let maxBalGudang = Math.floor(data.stokGudang / isi);
            let sisaPcsGudang = data.stokGudang % isi;
            
            let gudangText = `${maxBalGudang} ${data.unitBesar || 'Bal'}`;
            if (sisaPcsGudang > 0) gudangText += ` + ${sisaPcsGudang} ${data.unit || 'Pcs'}`;
            gudangText += ` (Total: ${data.stokGudang} ${data.unit || 'Pcs'})`;
            $('#infoGudang').text(gudangText);
            
            let maxBalToko = Math.floor(data.stokToko / isi);
            let sisaPcsToko = data.stokToko % isi;
            
            let tokoText = `${maxBalToko} ${data.unitBesar || 'Bal'}`;
            if (sisaPcsToko > 0) tokoText += ` + ${sisaPcsToko} ${data.unit || 'Pcs'}`;
            tokoText += ` (Total: ${data.stokToko} ${data.unit || 'Pcs'})`;
            $('#infoToko').text(tokoText);
            
            $('.big-unit-name').text(data.unitBesar || 'Bal');
            
            $('#conversionInfo').text("1 " + (data.unitBesar || 'Bal') + " = " + (data.isi || 1) + " " + (data.unit || 'Pcs'));
            
            // Validasi Input
            $('input[name="jumlah"]').attr('max', maxBalGudang).val('');
            
            $('#infoArea').slideDown();
        });

        $('input[name="jumlah"]').on('input change keyup', function() {
            let max = parseInt($(this).attr('max')) || 0;
            let val = parseInt($(this).val()) || 0;
            if (val > max) {
                $(this).val(max);
                Swal.fire({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                    icon: 'warning', title: 'Maks. Gudang: ' + max + ' Bal!'
                });
            }
        });

    });
</script>
@endsection
