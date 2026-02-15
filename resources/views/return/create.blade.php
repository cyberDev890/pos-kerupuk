@extends('layouts.app')
@section('content_title', 'Input Retur Baru')
@section('content')
<form action="{{ route('return.store') }}" method="POST" id="returnForm">
    @csrf
    <x-alert :errors="$errors" />
    <x-alert :type="'danger'" :errors="session('error')" />

    <div class="row">
        <!-- Top Section: Header Info -->
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                     <h3 class="card-title"><i class="fas fa-file-invoice"></i> Data Retur</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                             <div class="form-group">
                                 <label>Tanggal</label>
                                 <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                             </div>
                        </div>
                        <div class="col-md-3">
                             <div class="form-group">
                                 <label>Jenis Retur</label>
                                 <select name="jenis_retur" id="jenisRetur" class="form-control">
                                     <option value="penjualan">Retur Penjualan</option>
                                     <option value="pembelian">Retur Pembelian</option>
                                 </select>
                             </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                 <label>Cari Transaksi / Faktur</label>
                                 <select id="transactionSearch" class="form-control select2" style="width: 100%;"></select>
                                 <small class="text-muted">Ketik No Transaksi atau No Faktur</small>
                             </div>
                             <!-- Hidden inputs to store linked creation -->
                             <input type="hidden" name="transaction_id" id="transactionId">
                             <input type="hidden" name="purchase_id" id="purchaseId">
                             <input type="hidden" name="customer_id" id="customerId">
                             <input type="hidden" name="supplier_id" id="supplierId">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="callout callout-info" id="trxInfo" style="display: none;">
                                <h5>Detail Transaksi</h5>
                                <p id="trxDetailText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan / Alasan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Barang rusak, kadaluarsa, dll"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Selection Table -->
        <div class="col-md-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Pilih Barang yang Diretur</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px">Pilih</th>
                                <th>Produk</th>
                                <th>Qty Beli</th>
                                <th>Satuan</th>
                                <th>Harga Satuan</th>
                                <th style="width: 150px">Qty Retur</th>
                                <th>Info</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTable">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Silakan cari transaksi terlebih dahulu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right">Simpan Retur</button>
                    <a href="{{ route('return.index') }}" class="btn btn-default">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection

@section('scripts')
<script src="{{ asset('adminlte') }}/plugins/select2/js/select2.full.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for Transaction Search
        $('#transactionSearch').select2({
            theme: 'bootstrap4',
            placeholder: 'Ketik No Transaksi...',
            minimumInputLength: 0, // Allow showing list immediately
            ajax: {
                url: '{{ route("return.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        type: $('#jenisRetur').val()
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        // Handle Type Change
        $('#jenisRetur').change(function() {
            // Reset search when type changes
            $('#transactionSearch').val(null).trigger('change');
            $('#itemsTable').html('<tr><td colspan="7" class="text-center text-muted">Silakan cari transaksi terlebih dahulu</td></tr>');
            $('#trxInfo').hide();
            $('#transactionId').val('');
            $('#purchaseId').val('');
            $('#customerId').val('');
            $('#supplierId').val('');
        });

        // Handle Selection
        $('#transactionSearch').on('select2:select', function (e) {
            let data = e.params.data;
            let type = $('#jenisRetur').val();

            // Set Hidden Fields
            if(type == 'penjualan') {
                $('#transactionId').val(data.id);
                $('#purchaseId').val('');
                $('#customerId').val(data.customer_id);
                $('#supplierId').val('');
                $('#trxDetailText').html(`<strong>No:</strong> ${data.text} <br> <strong>Pelanggan:</strong> ${data.customer_name}`);
            } else {
                $('#transactionId').val('');
                $('#purchaseId').val(data.id);
                $('#customerId').val('');
                $('#supplierId').val(data.supplier_id);
                $('#trxDetailText').html(`<strong>No:</strong> ${data.text} <br> <strong>Supplier:</strong> ${data.supplier_name}`);
            }
            $('#trxInfo').show();

            // Render Items
            let html = '';
            if(data.details && data.details.length > 0) {
                data.details.forEach((item, index) => {
                    // Use server provided logic
                    let conversion = item.conversion; 
                    let unitType = item.unit_type;
                    let unitName = item.unit_name;

                    html += `
                        <tr>
                            <td class="text-center">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input item-check" type="checkbox" id="check_${index}" value="${index}">
                                    <label for="check_${index}" class="custom-control-label"></label>
                                </div>
                            </td>
                            <td>
                                ${item.nama_produk}
                                <input type="hidden" disabled name="cart[${index}][product_id]" value="${item.product_id}" class="item-input">
                                <input type="hidden" disabled name="cart[${index}][unit_id]" value="${item.unit_id}" class="item-input">
                                <input type="hidden" disabled name="cart[${index}][harga_satuan]" value="${item.harga_satuan}" class="item-input">
                                <input type="hidden" disabled name="cart[${index}][unit_info]" value="${item.unit_info || ''}" class="item-input">
                                <input type="hidden" disabled name="cart[${index}][conversion]" value="${conversion}" class="item-input">
                                <input type="hidden" disabled name="cart[${index}][unit_type]" value="${unitType}" class="item-input">
                            </td>
                            <td>${parseFloat(item.jumlah_beli)}</td>
                            <td>${unitName}</td>
                            <td>Rp ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}</td>
                            <td>
                                <input type="number" disabled name="cart[${index}][jumlah]" class="form-control form-control-sm item-qty item-input" 
                                    max="${item.jumlah_beli}" min="0.01" step="0.01" value="${parseFloat(item.jumlah_beli)}">
                            </td>
                            <td><small>${item.unit_info || '-'}</small></td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">Tidak ada item dalam transaksi ini</td></tr>';
            }
            $('#itemsTable').html(html);
        });

        // Handle Checkbox Toggle
        $(document).on('change', '.item-check', function() {
            let row = $(this).closest('tr');
            let isChecked = $(this).is(':checked');
            
            // Toggle inputs in this row
            row.find('.item-input').prop('disabled', !isChecked);
        });
        
        // Form Validation before submit
        $('#returnForm').submit(function(e){
             if($('.item-check:checked').length === 0) {
                 e.preventDefault();
                 alert('Pilih setidaknya satu barang untuk diretur!');
             }
        });

    });
</script>
@endsection
