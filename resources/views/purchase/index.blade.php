@extends('layouts.app')
@section('content_title', 'Riwayat Pembelian')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Riwayat Pembelian Produk</h4>
            <div>
                <a href="{{ route('transaction.purchase.create') }}" class="btn btn-primary px-4 font-weight-bold">
                    <i class="fas fa-plus mr-1"></i> INPUT PEMBELIAN BARU
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered" id="table2">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width: 50px">No</th>
                            <th class="text-center">Tanggal</th>
                            <th>No Faktur</th>
                            <th>Suplier</th>
                            <th class="text-right">Total Belanja</th>
                            <th class="text-center">Jumlah Item</th>
                            <th class="text-center" style="width: 150px">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchases as $index => $purchase)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">{{ date('d-m-Y', strtotime($purchase->tanggal)) }}</td>
                                <td class="font-weight-bold">{{ $purchase->no_faktur ?? '-' }}</td>
                                <td>{{ $purchase->supplier->nama }}</td>
                                <td class="text-right">Rp {{ number_format($purchase->total_harga) }}</td>
                                <td class="text-center">{{ $purchase->details_count }} Item</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-info btn-sm mr-1" onclick="showDetail({{ $purchase->id }})" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form action="{{ route('transaction.purchase.destroy', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini? Stok produk akan dikembalikan (dikurangi).');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Permanen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted italic">
                                    Belum ada riwayat pembelian tercatat.
                                </td>
                            </tr>
                        @endforelse
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
                    <h5 class="modal-title" id="modalDetailLabel">Detail Transaksi Pembelian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>No Faktur:</strong> <span id="detNoFaktur"></span><br>
                            <strong>Tanggal:</strong> <span id="detTanggal"></span><br>
                            <strong>Supplier:</strong> <span id="detSuplier"></span>
                        </div>
                        <div class="col-md-6 text-right">
                             <strong>Petugas:</strong> <span id="detUser"></span>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Satuan</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detItems">
                                <!-- Items Loaded via Ajax -->
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td colspan="4" class="text-right font-weight-bold h5">Total Transaksi</td>
                                    <td class="text-right font-weight-bold h5" id="detTotal"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3">
                        <strong>Keterangan/Catatan:</strong>
                        <p id="detKeterangan" class="mb-0 italic text-muted small">-</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function showDetail(id) {
        // Clear previous data
        $('#detItems').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $('#modalDetail').modal('show');
        
        // Fetch Data
        $.get("{{ url('transaction/purchase') }}/" + id, function(data) {
            $('#detNoFaktur').text(data.no_faktur || '-');
            $('#detTanggal').text(data.tanggal); 
            $('#detSuplier').text(data.supplier.nama);
            $('#detUser').text(data.user ? data.user.name : '-');
            $('#detKeterangan').text(data.keterangan || '-');
            
            let html = '';
            
            data.details.forEach(item => {
                let namaProduk = item.product ? item.product.nama_produk : 'Produk Dihapus (ID: ' + item.product_id + ')';
                let namaSatuan = item.unit ? (item.unit.satuan_besar ?? 'Unit') : '-';
                let unitInfo = item.unit_info ? `<br><small class="text-muted">${item.unit_info}</small>` : '';
                
                html += `
                    <tr>
                        <td>
                            ${namaProduk}
                            ${unitInfo}
                        </td>
                        <td>${namaSatuan}</td>
                        <td>${item.jumlah}</td> 
                        <td class="text-right">${formatRupiah(item.harga_satuan)}</td>
                        <td class="text-right">${formatRupiah(item.subtotal)}</td>
                    </tr>
                `;
            });
            
            $('#detItems').html(html);
            $('#detTotal').text(formatRupiah(data.total_harga));
        });
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }
</script>
@endsection
