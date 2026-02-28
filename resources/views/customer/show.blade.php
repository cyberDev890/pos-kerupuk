@extends('layouts.app')
@section('content_title', 'Detail Pelanggan')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Pelanggan</h3>
        <div class="card-tools">
            <a href="{{ route('master-data.customer.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 150px">Nama Pelanggan</th>
                        <td>: {{ $customer->nama }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>: {{ $customer->telepon }}</td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>: {{ $customer->alamat }}</td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td>: {{ $customer->keterangan }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pengaturan Harga Khusus</h3>
    </div>
    <div class="card-body p-0">
        <form action="{{ route('master-data.customer.storePrices', $customer->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover" id="table-harga-khusus">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center" width="35%">Satuan Kecil</th>
                            <th class="text-center" width="35%">Satuan Besar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $item)
                        @php
                            $isi = $item->isi ? $item->isi : 1;
                            $hargaBeliKecil = $item->harga_beli;
                            $hargaBeliBesar = $item->harga_beli * $isi;
                        @endphp
                        <tr>
                            <td class="align-middle">
                                <strong>{{ $item->nama_produk }}</strong><br>
                                <small class="text-muted">Isi: {{ $isi }} {{ $item->satuan_kecil }}/{{ $item->satuan_besar ?? 'Bal' }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                        <span>Normal: <span class="text-dark">{{ number_format($item->harga_jual, 0, ',', '.') }}</span></span>
                                        <span>Modal: <span class="text-danger">{{ number_format($hargaBeliKecil, 0, ',', '.') }}</span></span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control currency-input price-input" 
                                            data-modal="{{ $hargaBeliKecil }}"
                                            data-error="#error-kecil-{{ $item->id }}"
                                            name="prices[{{ $item->id }}][harga_jual]" 
                                            value="{{ number_format($item->khusus_kecil ?? $item->harga_jual, 0, ',', '.') }}" 
                                            placeholder="Harga Khusus (Kecil)">
                                    </div>
                                    <small id="error-kecil-{{ $item->id }}" class="text-danger mt-1 font-weight-bold error-msg"></small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                        <span>Normal: <span class="text-dark">{{ $item->harga_jual_besar ? number_format($item->harga_jual_besar, 0, ',', '.') : '-' }}</span></span>
                                        <span>Modal: <span class="text-danger">{{ number_format($hargaBeliBesar, 0, ',', '.') }}</span></span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control currency-input price-input" 
                                            data-modal="{{ $hargaBeliBesar }}"
                                            data-target="#error-besar-{{ $item->id }}"
                                            name="prices[{{ $item->id }}][harga_jual_besar]" 
                                            value="{{ number_format($item->khusus_besar ?? ($item->harga_jual_besar ?? 0), 0, ',', '.') }}" 
                                            placeholder="Harga Khusus (Besar)">
                                    </div>
                                     <small id="error-besar-{{ $item->id }}" class="text-danger mt-1 font-weight-bold error-msg"></small>
                                </div>
                            </td>
                             <input type="hidden" name="prices[{{ $item->id }}][product_id]" value="{{ $item->id }}">
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary float-right" id="btnSimpan" onclick="if($('.is-invalid').length > 0) { event.preventDefault(); $('#global-loader').hide(); Swal.fire('Error', 'Terdapat harga di bawah modal!', 'error'); } else { $('#global-loader').css('display', 'flex'); }">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#table-harga-khusus').DataTable({
            "paging": false,
            "scrollY": "500px",
            "scrollCollapse": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "language": {
                "search": "Cari Produk:",
                "info": "Menampilkan _TOTAL_ produk",
                "infoEmpty": "Tidak ada produk",
                "infoFiltered": "(difilter dari _MAX_ total produk)"
            }
        });

        // Validate Price on Input
        $(document).on('input', '.price-input', function() {
            validatePrice(this);
        });

        // Validate all on load
        $('.price-input').each(function() {
            if($(this).val()) {
                validatePrice(this);
            }
        });

        function validatePrice(element) {
            let inputVal = $(element).val().replace(/\./g, '').replace(/,/g, '.');
            let price = parseFloat(inputVal) || 0;
            let modal = parseFloat($(element).data('modal')) || 0;
            
            // Find error message element (sibling or by id)
            // Error id logic: #error-kecil-{id} or #error-besar-{id}
            // But strict ID selector is safer
            let name = $(element).attr('name');
            let isKecil = name.includes('harga_jual]'); // harga_jual (kecil) vs harga_jual_besar
            let row = $(element).closest('tr');
            
            // Simpler: use the data-error or data-target attribute I added in blade
             let targetId = $(element).data('error') || $(element).data('target');
            
            if (price > 0 && price < modal) {
                $(targetId).text('Harga di bawah modal!');
                $(element).addClass('is-invalid');
            } else {
                $(targetId).text('');
                $(element).removeClass('is-invalid');
            }
            
            checkSaveButton();
        }

        function checkSaveButton() {
            if ($('.is-invalid').length > 0) {
                $('#btnSimpan').prop('disabled', true);
            } else {
                $('#btnSimpan').prop('disabled', false);
            }
        }
    });
</script>
@endsection
