@extends('layouts.app')
@section('content_title', 'Data Produk')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Data Produk</h4>
            <div>
                <x-product.form-product />
            </div>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <table class="table table-sm" id="table2">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Harga Jual</th>
                        <th>Harga Beli</th>
                        <th>Stok Toko</th>
                        <th>Stok Gudang</th>
                        <th>Aktif</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->nama_produk }}</td>
                            <td>{{ $product->unit->nama_satuan ?? '-' }}</td>
                            <td>
                                <div>Rp.{{ number_format($product->harga_jual) }} / {{ $product->unit->satuan_kecil ?? 'Pcs' }}</div>
                                @if($product->harga_jual_besar)
                                    <div>Rp.{{ number_format($product->harga_jual_besar) }} / {{ $product->unit->satuan_besar ?? 'Unit' }}</div>
                                @endif
                            </td>
                            <td>
                                <div>Rp.{{ number_format($product->harga_beli) }} / {{ $product->unit->satuan_kecil ?? 'Pcs' }}</div>
                                @if($product->unit && $product->unit->isi > 1)
                                    <small class="text-muted">
                                        Rp.{{ number_format($product->harga_beli * $product->unit->isi) }} / {{ $product->unit->satuan_besar ?? 'Unit' }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{ floor($product->stok / ($product->unit->isi ?? 1)) }} {{ $product->unit->satuan_besar ?? '' }}
                                <small class="d-block text-muted">
                                    ({{ number_format($product->stok) }} {{ $product->unit->satuan_kecil ?? '' }})
                                </small>
                            </td>
                            <td>
                                {{ floor($product->stok_gudang / ($product->unit->isi ?? 1)) }} {{ $product->unit->satuan_besar ?? '' }}
                                <small class="d-block text-muted">
                                    ({{ number_format($product->stok_gudang) }} {{ $product->unit->satuan_kecil ?? '' }})
                                </small>
                            </td>
                            <td>
                                <p class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </p>
                            </td>
                            <td>    
                                <div class="d-flex align-items-center">
                                    <x-product.form-product :id="$product->id" />
                                    <a href="{{ route('master-data.product.destroy', $product->id) }}"
                                        class="btn btn-danger mx-1"data-confirm-delete="true">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
    </div>
@endsection

@section('scripts')
<script>
    function updateUnitLabels(e, id) {
        let selected = $(e).find(':selected');
        let kecil = selected.data('kecil') || 'Satuan Kecil';
        let besar = selected.data('besar') || 'Satuan Besar';

        $('#label-jual-' + id).text('Harga Jual (' + kecil + ')');
        $('#label-besar-' + id).text('Harga Jual Besar (' + besar + ')');
        $('#label-beli-' + id).text('Harga Beli (' + kecil + ')');
    }

    $(document).ready(function() {
        // Initialize all unit selects
        $('select[name="unit_id"]').each(function() {
            let idAttr = $(this).attr('id'); 
            if (idAttr) {
                let idSuffix = idAttr.replace('unit_id_', '');
                updateUnitLabels(this, idSuffix);
            }
        });
    });
</script>
@endsection
