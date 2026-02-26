@extends('layouts.app')
@section('content_title', 'Input Pembelian')
@section('content')
@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection
<form action="{{ route('transaction.purchase.store') }}" method="POST">
    @csrf
    <div class="row">
        <!-- Sidebar: Purchase Info -->
        <div class="col-lg-3">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Informasi Pembelian</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="text-muted small uppercase font-weight-bold">Suplier</label>
                        <select name="supplier_id" id="supplierSelect" class="form-control select2" required>
                            <option value="">Pilih Suplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="text-muted small uppercase font-weight-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="text-muted small uppercase font-weight-bold">No Faktur</label>
                        <input type="text" name="no_faktur" class="form-control bg-light" value="{{ $no_faktur }}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="text-muted small uppercase font-weight-bold">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Cart & Items -->
        <div class="col-lg-9">
            <!-- Item Selector -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="text-muted small uppercase font-weight-bold">Pilih Produk</label>
                            <select id="productSelect" class="form-control select2" style="width: 100%;">
                                <option value="">Cari Produk...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        data-unit-id="{{ $product->unit_id }}"
                                        data-unit-nama="{{ $product->unit->nama_satuan ?? '-' }}"
                                        data-unit-besar="{{ $product->unit->satuan_besar ?? 'Unit' }}"
                                        data-unit-kecil="{{ $product->unit->satuan_kecil ?? 'Pcs' }}"
                                        data-unit-isi="{{ $product->unit->isi ?? 1 }}"
                                        data-harga="{{ $product->harga_beli }}">
                                        {{ $product->nama_produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="text-muted small uppercase font-weight-bold">Satuan</label>
                            <input type="text" id="unitText" class="form-control bg-light text-center" readonly value="-">
                            <input type="hidden" id="unitId">
                        </div>
                        <div class="col-md-2">
                            <label class="text-muted small uppercase font-weight-bold">Jumlah</label>
                            <input type="number" id="qtyInput" class="form-control text-center font-weight-bold" value="1" min="1" inputmode="numeric">
                        </div>
                        <div class="col-md-3">
                            <label id="labelHarga" class="text-muted small uppercase font-weight-bold text-truncate">Harga Beli</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold text-sm">Rp</span>
                                </div>
                                <input type="text" id="priceInput" class="form-control currency-input font-weight-bold" value="0" inputmode="numeric">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="btnAddCart" class="btn btn-primary btn-block">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Cart Table -->
                <div class="col-md-8">
                    <div class="card shadow-sm min-vh-50">
                        <div class="card-header bg-white border-bottom-0">
                            <h3 class="card-title text-muted font-weight-bold">Daftar Barang</h3>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-top-0 py-3" style="width: 40%">Produk</th>
                                        <th class="border-top-0 py-3 text-center">Satuan</th>
                                        <th class="border-top-0 py-3 text-center">Qty</th>
                                        <th class="border-top-0 py-3 text-right">Harga</th>
                                        <th class="border-top-0 py-3 text-right">Subtotal</th>
                                        <th class="border-top-0 py-3 text-center" style="width: 50px"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartTable">
                                    <!-- Dynamic Items -->
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted italic">
                                            <i class="fas fa-shopping-basket fa-3x mb-3 d-block opacity-50"></i>
                                            Keranjang masih kosong
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary & Payment -->
                <div class="col-md-4">
                    <div class="card shadow-sm border-top border-primary">
                        <div class="card-body bg-light p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted font-weight-bold small uppercase">Total Tagihan</span>
                                <h3 class="mb-0 text-primary font-weight-bold" id="grandTotalDisplay">Rp. 0</h3>
                            </div>
                            <hr class="mt-2 mb-3">
                            
                            <div class="form-group mb-3">
                                <label class="text-muted small uppercase font-weight-bold d-block mb-1">Nominal Pembayaran</label>
                                <div class="input-group input-group-lg">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 font-weight-bold">Rp</span>
                                    </div>
                                    <input type="text" name="bayar" id="inputBayar" class="form-control border-left-0 font-weight-bold currency-input" placeholder="0" inputmode="numeric">
                                </div>
                            </div>
                            
                            <div class="bg-white p-3 rounded border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small" id="labelKembalian">Kekurangan</span>
                                    <span class="font-weight-bold text-danger" id="kembalianDisplay">Rp. 0</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm py-3 font-weight-bold" id="btnSubmit" disabled>
                                <i class="fas fa-save mr-2"></i> SIMPAN PEMBELIAN
                            </button>
                            
                            <p class="text-center text-muted small mt-3 mb-0">
                                Pastikan semua data sudah benar sebelum menyimpan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<!-- Select2 -->
<script src="{{ asset('adminlte') }}/plugins/select2/js/select2.full.min.js"></script>
<style>
    .min-vh-50 { min-height: 50vh; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    .opacity-50 { opacity: 0.5; }
    .currency-input { text-align: right; letter-spacing: 1px; }
    .select2-container--bootstrap4 .select2-selection--single { height: calc(2.25rem + 2px) !important; }
    .card-outline.card-primary { border-top: 3px solid #007bff; }
</style>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#productSelect, #supplierSelect').select2({
            theme: 'bootstrap4'
        });

        let cart = [];
        let grandTotal = 0;

        // Auto focus payload input on add
        function focusPrice() {
            setTimeout(() => { $('#priceInput').focus().select(); }, 100);
        }

        $('#productSelect').change(function() {
            let selected = $(this).find(':selected');
            if(selected.val()) {
                let unitBesar = selected.data('unit-besar');
                $('#unitText').val(unitBesar);
                $('#unitId').val(selected.data('unit-id'));
                $('#labelHarga').text('Harga Beli / ' + unitBesar);
                
                let price = selected.data('harga'); 
                let total = price * selected.data('unit-isi');
                $('#priceInput').val(new Intl.NumberFormat('id-ID').format(total)); 
                focusPrice();
            } else {
                $('#unitText').val('-');
                $('#unitId').val('');
                $('#labelHarga').text('Harga Beli');
                $('#priceInput').val(0);
            }
        });

        // Format currency on input
        $('.currency-input').on('keyup', function() {
            let val = $(this).val().replace(/\./g, '');
            if (!isNaN(val) && val !== '') {
                $(this).val(new Intl.NumberFormat('id-ID').format(val));
            }
        });

        $('#btnAddCart').click(function() {
            let productId = $('#productSelect').val();
            let unitId = $('#unitId').val();
            let qty = parseFloat($('#qtyInput').val());
            let priceRaw = $('#priceInput').val().replace(/\./g, '');
            let price = parseFloat(priceRaw);

            if (!productId || qty <= 0) {
                Swal.fire('Opps!', 'Pilih produk dan masukkan jumlah yang valid.', 'warning');
                return;
            }

            let subtotal = qty * price;
            
            // Add to Cart
            cart.push({
                product_id: productId,
                unit_id: unitId,
                jumlah: qty,
                harga_satuan: price,
                subtotal: subtotal
            });

            renderCart();
            
            // Reset Input
            $('#productSelect').val('').trigger('change');
            $('#qtyInput').val(1);
            $('#priceInput').val(0);
            $('#productSelect').select2('open');
        });

        function renderCart() {
            let html = '';
            grandTotal = 0;
            
            if (cart.length === 0) {
                html = `
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted italic">
                            <i class="fas fa-shopping-basket fa-3x mb-3 d-block opacity-50"></i>
                            Keranjang masih kosong
                        </td>
                    </tr>
                `;
                $('#btnSubmit').prop('disabled', true);
            } else {
                cart.forEach((item, index) => {
                    grandTotal += item.subtotal;
                    let selectedOpt = $(`#productSelect option[value="${item.product_id}"]`);
                    let productName = selectedOpt.text().trim();
                    let unitName = selectedOpt.data('unit-besar');

                    html += `
                        <tr>
                            <td class="align-middle font-weight-bold text-dark">
                                ${productName}
                                <input type="hidden" name="cart[${index}][product_id]" value="${item.product_id}">
                                <input type="hidden" name="cart[${index}][unit_id]" value="${item.unit_id}">
                            </td>
                            <td class="text-center align-middle"><span class="badge badge-light p-2">${unitName}</span></td>
                            <td class="text-center align-middle font-weight-bold">
                                ${item.jumlah}
                                <input type="hidden" name="cart[${index}][jumlah]" value="${item.jumlah}">
                            </td>
                            <td class="text-right align-middle text-muted">
                                Rp. ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}
                                <input type="hidden" name="cart[${index}][harga_satuan]" value="${item.harga_satuan}">
                            </td>
                            <td class="text-right align-middle font-weight-bold text-primary">Rp. ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-link text-danger" onclick="removeCart(${index})"><i class="fas fa-times-circle"></i></button>
                            </td>
                        </tr>
                    `;
                });
                $('#btnSubmit').prop('disabled', false);
            }
            
            $('#cartTable').html(html);
            $('#grandTotalDisplay').text('Rp. ' + new Intl.NumberFormat('id-ID').format(grandTotal));
            checkPayment();
        }

        $('#inputBayar').on('input', function() {
            checkPayment();
        });

        function checkPayment() {
            let bayarRaw = $('#inputBayar').val().replace(/\./g, '');
            let bayar = parseFloat(bayarRaw) || 0;

            if (grandTotal > 0) {
                if (bayar >= grandTotal) {
                    let kembalian = bayar - grandTotal;
                    $('#labelKembalian').text('Kembalian (Uang Kembali)');
                    $('#kembalianDisplay').text('Rp. ' + new Intl.NumberFormat('id-ID').format(kembalian));
                    $('#kembalianDisplay').removeClass('text-danger').addClass('text-success');
                    $('#btnSubmit').html('<i class="fas fa-check-circle mr-2"></i> SIMPAN (LUNAS)');
                    $('#btnSubmit').removeClass('btn-warning').addClass('btn-primary');
                } else {
                    let kurang = grandTotal - bayar;
                    $('#labelKembalian').text('Kekurangan (Hutang)');
                    $('#kembalianDisplay').text('Rp. ' + new Intl.NumberFormat('id-ID').format(kurang));
                    $('#kembalianDisplay').removeClass('text-success').addClass('text-danger');
                    $('#btnSubmit').html('<i class="fas fa-file-invoice-dollar mr-2"></i> SIMPAN (HUTANG)');
                    $('#btnSubmit').removeClass('btn-primary').addClass('btn-warning');
                }
            } else {
                $('#kembalianDisplay').text('Rp. 0');
                $('#btnSubmit').prop('disabled', true);
            }
        }

        $('form').on('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                Swal.fire('Kosong!', 'Mohon tambahkan setidaknya 1 produk.', 'error');
                return;
            }

            // Clean currency formatting before submit
            $('.currency-input').each(function() {
                let val = $(this).val().replace(/\./g, '');
                $(this).val(val);
            });
        });

        window.removeCart = function(index) {
            cart.splice(index, 1);
            renderCart();
        }
    });
</script>
@endsection
@endsection

