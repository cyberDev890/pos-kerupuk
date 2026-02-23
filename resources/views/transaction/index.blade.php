@extends('layouts.app')
@section('content_title', 'Riwayat Penjualan')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Riwayat Penjualan</h4>
            <div>
                <a href="{{ route('transaction.sales.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Transaksi Baru</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered" id="table2">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>No</th>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total Belanja</th>
                            <th>Biaya Kirim</th>
                            <th>Biaya Lain</th>
                            <th>Grand Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $index => $trx)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $trx->no_transaksi }}</td>
                                <td>{{ date('d-m-Y', strtotime($trx->tanggal)) }}</td>
                                <td>{{ $trx->customer->nama ?? 'Umum' }}</td>
                                <td>Rp {{ number_format($trx->total_harga - $trx->biaya_kirim - $trx->biaya_tambahan) }}</td>
                                <td>Rp {{ number_format($trx->biaya_kirim) }}</td>
                                <td>Rp {{ number_format($trx->biaya_tambahan) }}</td>
                                <td class="font-weight-bold">Rp {{ number_format($trx->total_harga) }}</td>
                                <td>
                                    <div class="d-flex">
                                        <button class="btn btn-info btn-sm mr-1" onclick="showDetail({{ $trx->id }})" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm btn-print-struk mr-1" data-id="{{ $trx->id }}" title="Cetak Struk">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        @if($trx->customer_id)
                                        <a href="{{ route('transaction.sales.invoice', $trx->id) }}" target="_blank" class="btn btn-secondary btn-sm mr-1" title="Cetak Invoice">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        @endif
                                        <!-- Delete Form -->
                                        <form action="{{ route('transaction.sales.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Stok akan dikembalikan.');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Batalkan/Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">Detail Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>No Transaksi:</strong> <span id="detNoFaktur"></span><br>
                            <strong>Tanggal:</strong> <span id="detTanggal"></span><br>
                            <strong>Pelanggan:</strong> <span id="detPelanggan"></span>
                        </div>
                        <div class="col-md-6 text-right">
                             <strong>Kasir:</strong> <span id="detKasir"></span>
                        </div>
                    </div>
                    
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detItems">
                            <!-- Items Loaded via Ajax -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right font-weight-bold">Total Belanja</td>
                                <td class="text-right" id="detTotalBelanja"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Biaya Kirim</td>
                                <td class="text-right" id="detBiayaKirim"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Biaya Tambahan</td>
                                <td class="text-right" id="detBiayaTambahan"></td>
                            </tr>
                            <tr class="bg-light">
                                <td colspan="3" class="text-right font-weight-bold h5">Grand Total</td>
                                <td class="text-right font-weight-bold h5" id="detGrandTotal"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Bayar</td>
                                <td class="text-right" id="detBayar"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Kembalian</td>
                                <td class="text-right" id="detKembalian"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="printReceipt()"><i class="fas fa-print"></i> Cetak</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showDetail(id) {
        currentTransactionId = id;
        // Clear previous data
        $('#detItems').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#modalDetail').modal('show');
        
        // Fetch Data
        $.get("/transaction/sales/" + id, function(data) {
            $('#detNoFaktur').text(data.no_transaksi);
            $('#detTanggal').text(data.tanggal); // Format date if needed
            $('#detPelanggan').text(data.customer ? data.customer.nama : 'Umum');
            $('#detKasir').text(data.user ? data.user.name : '-');
            
            let html = '';
            let subTotal = 0;
            
            data.details.forEach(item => {
                let namaProduk = item.product ? item.product.nama_produk : 'Produk Dihapus';
                let unitInfo = item.unit_info ? `<br><small class="text-muted">${item.unit_info}</small>` : '';
                
                html += `
                    <tr>
                        <td>
                            ${namaProduk}
                            ${unitInfo}
                        </td>
                        <td>${item.jumlah}</td> 
                        <td class="text-right">${formatRupiah(item.harga_satuan)}</td>
                        <td class="text-right">${formatRupiah(item.subtotal)}</td>
                    </tr>
                `;
            });
            
            $('#detItems').html(html);
            
            // Calculate Total Belanja (Grand Total - Fees)
            let grandTotal = parseFloat(data.total_harga);
            let biayaKirim = parseFloat(data.biaya_kirim);
            let biayaTambahan = parseFloat(data.biaya_tambahan);
            let totalBelanja = grandTotal - biayaKirim - biayaTambahan;
            
            $('#detTotalBelanja').text(formatRupiah(totalBelanja));
            $('#detBiayaKirim').text(formatRupiah(biayaKirim));
            $('#detBiayaTambahan').text(formatRupiah(biayaTambahan));
            $('#detGrandTotal').text(formatRupiah(grandTotal));
            $('#detBayar').text(formatRupiah(data.bayar));
            $('#detKembalian').text(formatRupiah(data.kembalian));
        });
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }
    
    function printReceipt() {
        if(currentTransactionId) {
            printRaw(currentTransactionId);
        }
    }

    $(document).ready(function() {
        // Event Delegation for Print Button
        $(document).on('click', '.btn-print-struk', function() {
            let id = $(this).data('id');
            printRaw(id);
        });
    });

    function printRaw(id) {
        let url = "{{ route('transaction.sales.print-raw', ':id') }}";
        url = url.replace(':id', id);
        
        // Open in new tab
        window.open(url, '_blank');
    }
    
    let currentTransactionId = null;
</script>
@endsection
