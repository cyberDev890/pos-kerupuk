@extends('layouts.app')
@section('content_title', 'Input Pembelian')
@section('content')
<form action="{{ route('transaction.purchase.store') }}" method="POST">
    @csrf
    <div class="row">
        <!-- Header Pembelian -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Pembelian</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Suplier</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">Pilih Suplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>No Faktur (Otomatis)</label>
                        <input type="text" name="no_faktur" class="form-control" value="{{ $no_faktur }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Produk -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Keranjang Belanja</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Produk</label>
                                <select id="productSelect" class="form-control select2" style="width: 100%;">
                                    <option value="">Pilih Produk</option>
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
                        </div>
                        <div class="col-md-2">
                             <div class="form-group">
                                <label>Satuan</label>
                                <input type="text" id="unitText" class="form-control" readonly placeholder="-">
                                <input type="hidden" id="unitId">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="number" id="qtyInput" class="form-control" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label id="labelHarga">Harga Beli (Per Satuan)</label>
                                <input type="text" id="priceInput" class="form-control currency-input" value="0">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" id="btnAddCart" class="btn btn-success btn-block"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Harga</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cartTable">
                                <!-- Cart Items -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th id="grandTotal">Rp. 0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Pembelian</button>
                </div>
            </div>
        </div>
    </div>
</form>

@section('scripts')
<script>
    $(document).ready(function() {
        let cart = [];
        let grandTotal = 0;

        $('#productSelect').change(function() {
            let selected = $(this).find(':selected');
            if(selected.val()) {
                let unitBesar = selected.data('unit-besar');
                $('#unitText').val(unitBesar);
                $('#unitId').val(selected.data('unit-id'));
                $('#labelHarga').text('Harga Beli (Per ' + unitBesar + ')');
                
                let price = selected.data('harga'); 
                // Harga di master adalah per Pcs, kita tampilkan estimasi per Bal
                let total = price * selected.data('unit-isi');
                $('#priceInput').val(new Intl.NumberFormat('id-ID').format(total)); 
            } else {
                $('#unitText').val('');
                $('#unitId').val('');
                $('#labelHarga').text('Harga Beli (Per Satuan)');
                $('#priceInput').val(0);
            }
        });

        $('#btnAddCart').click(function() {
            let productId = $('#productSelect').val();
            let productName = $('#productSelect option:selected').text().trim();
            let unitId = $('#unitId').val();
            let unitName = $('#unitText').val(); // Satuan Besar
            let qty = parseFloat($('#qtyInput').val());
            let priceRaw = $('#priceInput').val().replace(/\./g, '');
            let price = parseFloat(priceRaw);

            if (!productId || qty <= 0 || price < 0) {
                alert('Mohon lengkapi data produk, jumlah, dan harga.');
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
        });

        function renderCart() {
            let html = '';
            grandTotal = 0;
            
            cart.forEach((item, index) => {
                grandTotal += item.subtotal;
                let productName = $(`#productSelect option[value="${item.product_id}"]`).text().trim();
                let unitName = $(`#productSelect option[value="${item.product_id}"]`).data('unit-besar');

                html += `
                    <tr>
                        <td>
                            ${productName}
                            <input type="hidden" name="cart[${index}][product_id]" value="${item.product_id}">
                            <input type="hidden" name="cart[${index}][unit_id]" value="${item.unit_id}">
                        </td>
                        <td>${unitName}</td>
                        <td>
                            ${item.jumlah}
                            <input type="hidden" name="cart[${index}][jumlah]" value="${item.jumlah}">
                        </td>
                        <td>
                            Rp. ${new Intl.NumberFormat('id-ID').format(item.harga_satuan)}
                            <input type="hidden" name="cart[${index}][harga_satuan]" value="${item.harga_satuan}">
                        </td>
                        <td>Rp. ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeCart(${index})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
            
            $('#cartTable').html(html);
            $('#grandTotal').text('Rp. ' + new Intl.NumberFormat('id-ID').format(grandTotal));
        }

        window.removeCart = function(index) {
            cart.splice(index, 1);
            renderCart();
        }
    });
</script>
@endsection
@endsection
