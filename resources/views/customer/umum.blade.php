@extends('layouts.app')
@section('content_title', 'Set Harga Pelanggan Umum')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pengaturan Harga Pelanggan Umum (Harga Dasar)</h3>
        <div class="card-tools">
            <a href="{{ route('master-data.customer.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="alert alert-info m-3">
            <i class="fas fa-info-circle"></i> Perubahan harga di sini akan langsung memperbarui <strong>Harga Jual</strong> dan <strong>Harga Jual Besar</strong> di Master Produk.
        </div>
        <form action="{{ route('master-data.customer.storeUmumPrices') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover" id="table-harga-umum">
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
                                        <span>Modal: <span class="text-danger">{{ number_format($hargaBeliKecil, 0, ',', '.') }}</span></span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control currency-input price-input" 
                                            data-modal="{{ $hargaBeliKecil }}"
                                            data-target="#error-kecil-{{ $item->id }}"
                                            name="prices[{{ $item->id }}][harga_jual]" 
                                            value="{{ number_format($item->harga_jual, 0, ',', '.') }}" 
                                            placeholder="Harga Jual (Kecil)">
                                    </div>
                                    <small id="error-kecil-{{ $item->id }}" class="text-danger mt-1 font-weight-bold error-msg"></small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="d-flex justify-content-between text-xs text-muted mb-1">
                                        <span>Modal: <span class="text-danger">{{ number_format($hargaBeliBesar, 0, ',', '.') }}</span></span>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control currency-input price-input" 
                                            data-modal="{{ $hargaBeliBesar }}"
                                            data-target="#error-besar-{{ $item->id }}"
                                            name="prices[{{ $item->id }}][harga_jual_besar]" 
                                            value="{{ $item->harga_jual_besar ? number_format($item->harga_jual_besar, 0, ',', '.') : '' }}" 
                                            placeholder="Harga Jual (Besar)">
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
                <button type="submit" class="btn btn-primary float-right" id="btnSimpan">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#table-harga-umum').DataTable({
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

        // Format Currency on input
        $(document).on('input', '.currency-input', function() {
            let val = $(this).val().replace(/\./g, '');
            if (val !== '') {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
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

        // Track changes to optimize save (only send changed rows)
        $(document).on('change', '.price-input', function() {
            $(this).closest('tr').addClass('row-changed');
        });

        // Before submit, disable inputs for rows that didn't change
        $('form').on('submit', function() {
            // If validation fails, don't do anything (though button should be disabled)
            if ($('.is-invalid').length > 0) return false;

            // Disable all inputs in rows that DON'T have .row-changed
            $('#table-harga-umum tbody tr').each(function() {
                if (!$(this).hasClass('row-changed')) {
                    $(this).find('input').prop('disabled', true);
                }
            });

            // If no rows changed, prevent submission
            if ($('.row-changed').length === 0) {
                Swal.fire('Info', 'Tidak ada perubahan harga yang dideteksi.', 'info');
                // Re-enable so user can try again
                $('#table-harga-umum').find('input').prop('disabled', false);
                return false;
            }

            $('#global-loader').css('display', 'flex');
            return true;
        });

        function validatePrice(element) {
            let inputVal = $(element).val().replace(/\./g, '').replace(/,/g, '.');
            let price = parseFloat(inputVal) || 0;
            let modal = parseFloat($(element).data('modal')) || 0;
            
            let targetId = $(element).data('target');
            
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
