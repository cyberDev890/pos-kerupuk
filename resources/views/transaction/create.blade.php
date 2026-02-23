@extends('layouts.app')
@section('content_title', 'Kasir / Penjualan')
@section('content')
<form action="{{ route('transaction.sales.store') }}" method="POST">
    @csrf
    <x-alert :errors="$errors" />
    <x-alert :type="'danger'" :errors="session('error')" />
    @if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-check"></i> Berhasil!</h5>
        {{ session('success') }}
        @if(session('last_transaction_id'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let transactionId = {{ session('last_transaction_id') }};
                    let showModal = {{ session('show_print_modal') ? 'true' : 'false' }};

                    if (showModal) {
                        // Show Choice
                        Swal.fire({
                            title: 'Transaksi Berhasil!',
                            text: "Ingin mencetak bukti pembayaran?",
                            icon: 'success',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: '<i class="fas fa-print"></i> Struk Thermal',
                            denyButtonText: '<i class="fas fa-file-alt"></i> Nota Besar',
                            cancelButtonText: 'Tutup',
                            confirmButtonColor: '#28a745',
                            denyButtonColor: '#17a2b8'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                printRaw(transactionId);
                            } else if (result.isDenied) {
                                let url = "{{ route('transaction.sales.invoice', ':id', false) }}";
                                url = url.replace(':id', transactionId);
                                window.open(url, "_blank");
                            }
                        });
                    } else {
                        // Auto Print Thermal (Umum)
                        printRaw(transactionId);
                        
                        // Optional: Show simple success toast or alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Transaksi disimpan & Struk dicetak otomatis.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });

                function printRaw(id) {
                    let url = "{{ route('transaction.sales.print-raw', ':id') }}";
                    url = url.replace(':id', id);
                    
                    // Open in new tab
                    window.open(url, '_blank');
                }
            </script>
        @endif
    </div>
    @endif
    
    <!-- CARD 0: Customer & Date (Moved to Top) -->
    <div class="card card-info card-outline mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-tag"></i> Data Transaksi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                     <div class="form-group">
                        <label>Pelanggan</label>
                        <select name="customer_id" id="customerSelect" class="form-control select2">
                            <option value="">Umum (General)</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->nama }}</option>
                            @endforeach
                        </select>
                         <small class="text-muted text-info" id="customerInfo" style="display: none;">
                            <i class="fas fa-info-circle"></i> Fitur biaya tambahan aktif untuk pelanggan ini.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                         <label>Tanggal</label>
                         <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 1: Product Selection -->
    <div class="card card-primary card-outline mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search"></i> Cari Produk</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                     <div class="form-group mb-5">
                        <select id="productSelect" class="form-control select2" style="width: 100%;">
                            <option value="">Scan Barcode / Cari Produk...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" 
                                    data-nama="{{ $product->nama_produk }}"
                                    data-unit-id="{{ $product->unit_id }}"
                                    data-unit-besar="{{ optional($product->unit)->satuan_besar ?? 'Unit' }}"
                                    data-unit-kecil="{{ optional($product->unit)->satuan_kecil ?? 'Pcs' }}"
                                    data-isi="{{ optional($product->unit)->isi ?? 1 }}"
                                    data-harga-kecil="{{ $product->harga_jual }}"
                                    data-harga-besar="{{ $product->harga_jual_besar ?? ($product->harga_jual * (optional($product->unit)->isi ?? 1)) }}"
                                    data-stok="{{ $product->stok }}">
                                    {{ $product->nama_produk }} - Stok: {{ $product->stok }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Hidden Detail Area for Selected Product -->
                <div class="col-md-12 mt-3" id="productDetail" style="display: none;">
                    <div class="card bg-light border mb-0">
                        <div class="card-body py-2">
                             <div class="row align-items-center">
                                <div class="col-md-4">
                                     <strong id="detailNama" class="h5">Nama Produk</strong>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Stok Tersedia:</small>
                                        <span id="stockDisplay" class="font-weight-bold text-primary">-</span>
                                    </div>
                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                        <label class="btn btn-outline-primary active btn-sm" id="btnSatuanKecil">
                                            <input type="radio" name="options" id="optKecil" autocomplete="off" checked> 
                                            <span id="labelKecil">Pcs</span> (<span id="priceKecil">0</span>)
                                        </label>
                                        <label class="btn btn-outline-primary btn-sm" id="btnSatuanBesar">
                                            <input type="radio" name="options" id="optBesar" autocomplete="off"> 
                                            <span id="labelBesar">Bal</span> (<span id="priceBesar">0</span>)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                     <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <button type="button" class="btn btn-danger" onclick="changeQty(-1)"><i class="fas fa-minus"></i></button>
                                        </div>
                                        <input type="number" id="qtyInput" class="form-control text-center" value="1" min="1">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success" onclick="changeQty(1)"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                     <button type="button" id="btnAddCart" class="btn btn-success btn-sm btn-block">
                                        <i class="fas fa-plus"></i> Tambah
                                     </button>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARD 2: Cart Table -->
    <div class="card card-dark card-outline mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h3>
        </div>
        <div class="card-body p-0">
             <table class="table table-bordered table-striped mb-0">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>Produk</th>
                        <th>Satuan</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="cartTable">
                    <!-- Items go here -->
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="4" class="text-right font-weight-bold">Total Belanja:</td>
                        <td colspan="2" class="font-weight-bold text-primary h4" id="subTotalDisplay">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- CARD 3: Payment & Checkout -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Pembayaran</h3>
        </div>
        <div class="card-body">
             <div class="row">
                <div class="col-md-6">
                     <table class="table table-sm table-borderless">
                         <tr>
                             <th>Total Belanja</th>
                             <td class="text-right font-weight-bold" id="summarySubtotal">Rp 0</td>
                         </tr>
                             <tr>
                                 <th class="align-middle">Biaya Kirim</th>
                                 <td>
                                     <input type="text" name="biaya_kirim" id="inputBiayaKirim" class="form-control form-control-sm text-right fee-input currency-input" value="0" disabled placeholder="0">
                                 </td>
                             </tr>
                             <tr>
                                 <th class="align-middle">Biaya Tambahan</th>
                                 <td>
                                     <input type="text" name="biaya_tambahan" id="inputBiayaTambahan" class="form-control form-control-sm text-right fee-input currency-input" value="0" disabled placeholder="0">
                                 </td>
                             </tr>
                             <tr class="bg-light border-top">
                                 <th class="h4 align-middle">Grand Total</th>
                                 <td class="text-right h4 font-weight-bold text-primary" id="grandTotalDisplay">Rp 0</td>
                             </tr>
                         </table>
                    </div>
                    <div class="col-md-6">
                         <div class="card bg-light border">
                             <div class="card-body p-3">
                                 <div class="form-group">
                                     <label>Bayar (Rp)</label>
                                     <input type="text" name="bayar" id="inputBayar" class="form-control form-control-lg currency-input" placeholder="0">
                                </div>
                             <div class="d-flex justify-content-between mb-3">
                                <span>Kembalian:</span>
                                <span class="h5 font-weight-bold text-success" id="kembalianDisplay">Rp 0</span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnCheckout" disabled>
                                <i class="fas fa-save"></i> Proses Transaksi
                            </button>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
@endsection

@section('scripts')
<!-- Select2 -->
<script src="{{ asset('adminlte') }}/plugins/select2/js/select2.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        let cart = [];
        let subTotal = 0;
        let grandTotal = 0;
        
        let currentProduct = null;
        let selectedUnitType = 'kecil'; 

        let customerPrices = {}; // { productId: { khusus_kecil: 1000, khusus_besar: 2000 } }

        // Customer Selection Logic
        $('#customerSelect').change(function() {
            let val = $(this).val();
            if(val) {
                // Customer selected
                $('#inputBiayaKirim').prop('disabled', false);
                $('#inputBiayaTambahan').prop('disabled', false);
                $('#customerInfo').show();
                
                // Fetch Prices
                $.get('/master-data/customer/' + val + '/prices', function(data) {
                    customerPrices = {};
                    data.forEach(item => {
                         customerPrices[item.id] = {
                             khusus_kecil: item.khusus_kecil,
                             khusus_besar: item.khusus_besar
                         };
                    });
                    
                    // If a product is currently selected, refresh its display
                    if(currentProduct) {
                         $('#productSelect').trigger('select2:select');
                    }
                });
                
            } else {
                // Umum
                $('#inputBiayaKirim').prop('disabled', true).val(0);
                $('#inputBiayaTambahan').prop('disabled', true).val(0);
                $('#customerInfo').hide();
                customerPrices = {};
                
                if(currentProduct) {
                     $('#productSelect').trigger('select2:select');
                }
            }
            calculateTotals();
        });

        $('.fee-input').on('keyup change', function() {
            calculateTotals();
        });

        // Product Selection
        $('#productSelect').on('select2:select', function (e) {
            let selected = $(this).find(':selected');
             if(selected.val()) {
                currentProduct = {
                    id: selected.val(),
                    nama: selected.data('nama'),
                    unitId: selected.data('unit-id'),
                    unitBesar: selected.data('unit-besar'),
                    unitKecil: selected.data('unit-kecil'),
                    isi: selected.data('isi'),
                    hargaKecil: selected.data('harga-kecil'),
                    hargaBesar: selected.data('harga-besar'),
                    stok: selected.data('stok')
                };

                // Update UI
                $('#detailNama').text(currentProduct.nama);
                
                // Logic Harga Khusus
                let hargaKecil = currentProduct.hargaKecil;
                let hargaBesar = currentProduct.hargaBesar;
                
                // Check if customerPrices has this product
                if (customerPrices && customerPrices[currentProduct.id]) {
                    let cp = customerPrices[currentProduct.id];
                    if(cp.khusus_kecil && cp.khusus_kecil > 0) {
                        hargaKecil = parseFloat(cp.khusus_kecil);
                        $('#priceKecil').addClass('text-success font-weight-bold');
                    } else {
                        $('#priceKecil').removeClass('text-success font-weight-bold');
                    }
                    
                    if(cp.khusus_besar && cp.khusus_besar > 0) {
                        hargaBesar = parseFloat(cp.khusus_besar);
                        $('#priceBesar').addClass('text-success font-weight-bold');
                    } else {
                        $('#priceBesar').removeClass('text-success font-weight-bold');
                    }
                } else {
                     $('#priceKecil').removeClass('text-success font-weight-bold');
                     $('#priceBesar').removeClass('text-success font-weight-bold');
                }
                
                // Override currentProduct prices for calculation
                currentProduct.activeHargaKecil = hargaKecil;
                currentProduct.activeHargaBesar = hargaBesar;

                $('#labelKecil').text(currentProduct.unitKecil);
                $('#labelBesar').text(currentProduct.unitBesar);
                $('#priceKecil').text(formatRupiah(hargaKecil));
                $('#priceBesar').text(formatRupiah(hargaBesar));

                // Default selection
                setUnitType('kecil');
                $('#qtyInput').val(1);
                $('#productDetail').slideDown();
            } else {
                $('#productDetail').slideUp();
                currentProduct = null;
            }
        });

        // Unit Selection
        $('#btnSatuanKecil').click(function() { setUnitType('kecil'); });
        $('#btnSatuanBesar').click(function() { setUnitType('besar'); });

        function setUnitType(type) {
            selectedUnitType = type;
            if(type === 'kecil') {
                $('#btnSatuanKecil').addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#btnSatuanBesar').removeClass('active').addClass('btn-outline-primary').removeClass('btn-primary');
            } else {
                $('#btnSatuanBesar').addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#btnSatuanKecil').removeClass('active').addClass('btn-outline-primary').removeClass('btn-primary');
            }
            updateStockDisplay();
        }

        // Update Stock Display & Max Input
        function updateStockDisplay() {
            if(!currentProduct) return;

            let stock = currentProduct.stok;
            let displayStock = 0;
            let unitName = '';
            let maxQty = 0;

            if (selectedUnitType === 'kecil') {
                // Logic Pcs: Full Stock available
                displayStock = stock;
                unitName = currentProduct.unitKecil;
                maxQty = stock; // Can buy up to total pieces
            } else {
                // Logic Ball: Floor(Stock / Isi)
                let isi = currentProduct.isi > 1 ? currentProduct.isi : 1;
                displayStock = Math.floor(stock / isi);
                unitName = currentProduct.unitBesar;
                maxQty = displayStock; // Can only buy full units
            }

            $('#stockDisplay').text(displayStock + ' ' + unitName);
            $('#qtyInput').attr('max', maxQty);
            
            // Validate current input
            let currentVal = parseInt($('#qtyInput').val()) || 1;
            if (currentVal > maxQty) {
                $('#qtyInput').val(maxQty > 0 ? maxQty : 1); 
            }
        }

        window.changeQty = function(diff) {
            let val = parseInt($('#qtyInput').val()) || 1;
            let max = parseInt($('#qtyInput').attr('max')) || 999999;
            
            val += diff;
            
            if(val < 1) val = 1;
            if(val > max) val = max;
            
            $('#qtyInput').val(val);
        }

        // Add to Cart
        $('#btnAddCart').click(function() {
            if(!currentProduct) return;

            let qty = parseInt($('#qtyInput').val());
            let price = selectedUnitType === 'kecil' ? currentProduct.activeHargaKecil : currentProduct.activeHargaBesar;
            let itemSubtotal = qty * price;
            let unitName = selectedUnitType === 'kecil' ? currentProduct.unitKecil : currentProduct.unitBesar;

            cart.push({
                product_id: currentProduct.id,
                nama: currentProduct.nama,
                real_unit_id: currentProduct.unitId,
                unit_name: unitName,
                unit_type: selectedUnitType,
                jumlah: qty,
                harga_satuan: price,
                subtotal: itemSubtotal
            });
            
            renderCart();
            
            // Reset
            $('#qtyInput').val(1);
            $('#productSelect').val(null).trigger('change'); // Reset Select2
            $('#productDetail').slideUp(); 
            currentProduct = null;
        });

        function renderCart() {
            let html = '';
            subTotal = 0;
            
            cart.forEach((item, index) => {
                subTotal += item.subtotal;
                
                html += `
                    <tr>
                        <td>
                            ${item.nama}
                            <input type="hidden" name="cart[${index}][product_id]" value="${item.product_id}">
                            <input type="hidden" name="cart[${index}][unit_id]" value="${item.real_unit_id}">
                            <input type="hidden" name="cart[${index}][unit_type]" value="${item.unit_type}">
                            <input type="hidden" name="cart[${index}][jumlah]" value="${item.jumlah}">
                            <input type="hidden" name="cart[${index}][harga_satuan]" value="${item.harga_satuan}">
                        </td>
                        <td>${item.unit_name}</td>
                        <td>${formatRupiah(item.harga_satuan)}</td>
                        <td>${item.jumlah}</td>
                        <td>${formatRupiah(item.subtotal)}</td>
                        <td>
                             <button type="button" class="btn btn-sm btn-danger" onclick="removeCart(${index})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
            
            $('#cartTable').html(html);
            $('#subTotalDisplay').text(formatRupiah(subTotal));
            
            calculateTotals();
        }

        function calculateTotals() {
            let biayaKirimRaw = $('#inputBiayaKirim').val().replace(/\./g, '');
            let biayaKirim = parseFloat(biayaKirimRaw) || 0;
            
            let biayaTambahanRaw = $('#inputBiayaTambahan').val().replace(/\./g, '');
            let biayaTambahan = parseFloat(biayaTambahanRaw) || 0;
            
            // Only count fees if not disabled (meaning customer is selected)
            if($('#inputBiayaKirim').prop('disabled')) {
                biayaKirim = 0;
                biayaTambahan = 0;
            }

            grandTotal = subTotal + biayaKirim + biayaTambahan;

            $('#summarySubtotal').text(formatRupiah(subTotal));
            // Removed text update for summaryBiayaKirim since they are inputs now
            // But we might want to update grand total text
            $('#grandTotalDisplay').text(formatRupiah(grandTotal));

            checkPayment();
        }

        window.removeCart = function(index) {
            cart.splice(index, 1);
            renderCart();
        }

        $('#qtyInput').on('keyup change', function() {
            let val = parseInt($(this).val()) || 1;
            let max = parseInt($(this).attr('max')) || 999999;
            
            if(val > max) {
                $(this).val(max);
            }
            if(val < 1) {
                $(this).val(1);
            }
        });

        $('#inputBayar').on('keyup change input', function() {
            checkPayment();
        });

        function checkPayment() {
            let bayarRaw = $('#inputBayar').val().replace(/\./g, '');
            let bayar = parseFloat(bayarRaw) || 0;
            let customerId = $('#customerSelect').val();
            
            if(grandTotal > 0) {
                // Allow checkout if fully paid OR if customer is selected (Debt allowed)
                if (bayar >= grandTotal || customerId) {
                    $('#btnCheckout').prop('disabled', false);
                } else {
                    $('#btnCheckout').prop('disabled', true);
                }

                if(bayar < grandTotal) {
                    // Cek Customer
                    if(!customerId) {
                        $('#btnCheckout').html('<i class="fas fa-ban"></i> Umum Tidak Boleh Hutang');
                        $('#btnCheckout').removeClass('btn-primary btn-warning').addClass('btn-danger');
                        $('#btnCheckout').prop('disabled', true); // BLOCK SUBMIT
                        
                        $('#kembalianDisplay').text('Kurang: ' + formatRupiah(grandTotal - bayar));
                        $('#kembalianDisplay').removeClass('text-success').addClass('text-danger');
                    } else {
                        $('#btnCheckout').html('<i class="fas fa-save"></i> Simpan (Hutang)');
                        $('#btnCheckout').removeClass('btn-primary btn-danger').addClass('btn-warning');
                        
                        $('#kembalianDisplay').text('Kurang: ' + formatRupiah(grandTotal - bayar));
                        $('#kembalianDisplay').removeClass('text-success').addClass('text-danger');
                    }
                } else {
                    $('#btnCheckout').html('<i class="fas fa-save"></i> Proses Transaksi');
                    $('#btnCheckout').removeClass('btn-warning btn-danger').addClass('btn-primary');
                    $('#kembalianDisplay').removeClass('text-danger').addClass('text-success');
                    let kembalian = bayar - grandTotal;
                    $('#kembalianDisplay').text(formatRupiah(kembalian));
                }
            } else {
                $('#btnCheckout').prop('disabled', true);
            }
        }

        // Add event listener for Customer Change to re-check payment button validity
        $('#customerSelect').change(function() {
             checkPayment();
        });

        $('form').on('submit', function(e) {
            let bayarRaw = $('#inputBayar').val().replace(/\./g, '');
            let bayar = parseFloat(bayarRaw) || 0;
            let customerId = $('#customerSelect').val();
            
            if(bayar < grandTotal) {
                e.preventDefault();
                
                if(!customerId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Boleh Hutang!',
                        text: 'Pelanggan UMUM wajib lunas. Harap pilih nama pelanggan jika ingin mencatat hutang.',
                    });
                    return;
                }

                let kurang = grandTotal - bayar;
                
                // Confirm Debt
                Swal.fire({
                    title: 'Pembayaran Kurang!',
                    text: "Sisa pembayaran Rp " + formatRupiah(kurang) + " akan dicatat sebagai PIUTANG. Lanjutkan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan Piutang!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Unbind submit to prevent loop and submit programmatically
                        e.currentTarget.submit();
                    }
                });
            }
        });

        function formatRupiah(angka) {
             return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }
    });
</script>
@endsection
