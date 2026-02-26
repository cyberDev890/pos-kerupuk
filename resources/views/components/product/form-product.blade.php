<div>
    <button type="button" class="btn {{ ($id ?? false) ? 'btn-warning' : 'btn-primary' }}" data-toggle="modal"
        data-target="#formProduct{{ $id ?? '' }}">
        @if ($id)
            <i class="fas fa-edit"></i>
        @else
            Produk Baru
        @endif
    </button>

    <div class="modal fade" id="formProduct{{ $id ?? '' }}">
        <form action="{{ route('master-data.product.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ ($id ?? false) ? 'Form Edit Produk' : 'Form Tambah Produk' }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group my-1">
                            <label for="nama_produk_{{ $id ?? 'new' }}"> Nama Produk</label>
                            <input type="text" class="form-control" name="nama_produk" id="nama_produk_{{ $id ?? 'new' }}"
                                value="{{ old('nama_produk', $nama_produk ?? '') }}"
                                placeholder="Masukkan Nama Produk ">
                        </div>
                        <div class="form-group my-1">
                            <label for="unit_id_{{ $id ?? 'new' }}"> Satuan</label>
                            <select name="unit_id" id="unit_id_{{ $id ?? 'new' }}" class="form-control" onchange="updateUnitLabels(this, '{{ $id ?? 'new' }}'); updateStokHelper('{{ $id ?? 'new' }}')">
                                <option value="">Pilih Satuan</option>
                                @foreach ($units ?? [] as $unit)
                                    <option value="{{ $unit->id }}"
                                        data-kecil="{{ $unit->satuan_kecil }}"
                                        data-besar="{{ $unit->satuan_besar }}"
                                        data-isi="{{ $unit->isi }}"
                                        {{ old('unit_id', $unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->nama_satuan }} (1 {{ $unit->satuan_besar }} = {{ $unit->isi }} {{ $unit->satuan_kecil }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group my-1">
                            <label for="harga_beli_{{ $id ?? 'new' }}" id="label-beli-{{ $id ?? 'new' }}">Harga Beli</label>
                            <input type="text" class="form-control currency-input" name="harga_beli" id="harga_beli_{{ $id ?? 'new' }}"
                                value="{{ number_format(old('harga_beli', $harga_beli ?? 0), 0, ',', '.') }}" placeholder="Masukkan Harga Beli " onblur="validateProductForm('{{ $id ?? 'new' }}')" inputmode="numeric">
                            <div class="invalid-feedback" id="error-harga-beli-{{ $id ?? 'new' }}"></div>
                        </div>
                        <div class="form-group my-1">
                            <label for="harga_jual_{{ $id ?? 'new' }}" id="label-jual-{{ $id ?? 'new' }}">Harga Jual (Satuan Kecil)</label>
                            <input type="text" class="form-control currency-input" name="harga_jual" id="harga_jual_{{ $id ?? 'new' }}"
                                value="{{ number_format(old('harga_jual', $harga_jual ?? 0), 0, ',', '.') }}" placeholder="Masukkan Harga Jual" onblur="validateProductForm('{{ $id ?? 'new' }}')" inputmode="numeric">
                            <div class="invalid-feedback" id="error-harga-jual-{{ $id ?? 'new' }}"></div>
                        </div>
                        <div class="form-group my-1">
                            <label for="harga_jual_besar_{{ $id ?? 'new' }}" id="label-besar-{{ $id ?? 'new' }}">Harga Jual Besar (Satuan Besar)</label>
                            <input type="text" class="form-control currency-input" name="harga_jual_besar" id="harga_jual_besar_{{ $id ?? 'new' }}"
                                value="{{ number_format(old('harga_jual_besar', $harga_jual_besar ?? 0), 0, ',', '.') }}" placeholder="Masukkan Harga Jual Besar" onblur="validateProductForm('{{ $id ?? 'new' }}')" inputmode="numeric">
                            <div class="invalid-feedback" id="error-harga-jual-besar-{{ $id ?? 'new' }}"></div>
                        </div>
                        <div class="form-group my-1">
                            <label for="stok_{{ $id ?? 'new' }}">Stok Toko</label>
                            <input type="number" step="any" class="form-control" name="stok" id="stok_{{ $id ?? 'new' }}"
                                value="{{ old('stok', $stok ?? 0) }}" placeholder="Masukkan Stok Real" oninput="updateStokHelper('{{ $id ?? 'new' }}')" inputmode="numeric">
                            <small class="text-muted mb-1 d-block">Mengubah nilai ini akan menimpa stok yang ada.</small>
                            <small class="text-info font-weight-bold" id="stok-helper-{{ $id ?? 'new' }}"></small>
                        </div>
                        <div class="form-group my-1">
                            <label for="stok_gudang_{{ $id ?? 'new' }}">Stok Gudang (Satuan Besar / Ball)</label>
                            <input type="number" step="any" class="form-control" name="stok_gudang" id="stok_gudang_{{ $id ?? 'new' }}"
                                value="{{ old('stok_gudang', $stok_gudang ?? 0) }}" placeholder="Masukkan Stok Gudang" inputmode="numeric">
                        </div>
                        <div class="form-group my-1">
                            <label for="stok_min_{{ $id ?? 'new' }}">Stok Minimal (Satuan Kecil)</label>
                            <input type="number" step="any" class="form-control" name="stok_min" id="stok_min_{{ $id ?? 'new' }}"
                                value="{{ old('stok_min', $stok_min ?? 0) }}" placeholder="Masukkan Stok Minimal (Pcs)" inputmode="numeric">
                        </div>
                        <div class="form-group my-1 d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <label for="is_active_{{ $id ?? 'new' }}" class="mr-4"> Produk Aktif</label>
                                <input type="checkbox" name="is_active" id="is_active_{{ $id ?? 'new' }}"
                                    {{ old('is_active', $is_active ?? false) ? 'checked' : '' }}>
                            </div>
                            <small class="text-secondary -mt-2"> Jika Aktif, Produk akan muncul di halaman
                                kasir</small>

                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" onclick="return validateProductForm('{{ $id ?? 'new' }}')">Save changes</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </form>
    </div>
</div>

<script>
    function validateProductForm(id) {
        let unitSelect = document.getElementById('unit_id_' + id);
        let selectedOption = unitSelect.options[unitSelect.selectedIndex];
        let isi = 1;
        
        if (selectedOption && selectedOption.value) {
            let text = selectedOption.text;
            let match = text.match(/=\s*(\d+)/);
            if (match) {
                isi = parseInt(match[1]);
            }
        }

        let elHargaBeli = document.getElementById('harga_beli_' + id);
        let elHargaJual = document.getElementById('harga_jual_' + id);
        let elHargaJualBesar = document.getElementById('harga_jual_besar_' + id);

        let hargaBeli = parseFloat(elHargaBeli.value.replace(/\./g, '').replace(/,/g, '.')) || 0;
        let hargaJual = parseFloat(elHargaJual.value.replace(/\./g, '').replace(/,/g, '.')) || 0;
        let hargaJualBesar = parseFloat(elHargaJualBesar.value.replace(/\./g, '').replace(/,/g, '.')) || 0;

        // Reset Validation
        elHargaBeli.classList.remove('is-invalid');
        elHargaJualBesar.classList.remove('is-invalid');

        let isValid = true;

        if (hargaBeli > hargaJual) {
            elHargaBeli.classList.add('is-invalid');
            document.getElementById('error-harga-beli-' + id).textContent = 'Harga Beli harus lebih kecil atau sama dengan Harga Jual!';
            isValid = false;
        }

        if (hargaJualBesar > 0) {
            let modalBesar = hargaBeli * isi;
            if (modalBesar > hargaJualBesar) {
                elHargaJualBesar.classList.add('is-invalid');
                document.getElementById('error-harga-jual-besar-' + id).textContent = 'Harga Jual Besar tidak boleh kurang dari modal per bal (Rp ' + new Intl.NumberFormat('id-ID').format(modalBesar) + ')!';
                isValid = false;
            }
        }

        return isValid;
    }

    function updateStokHelper(id) {
        let stockInput = document.getElementById('stok_' + id);
        let unitSelect = document.getElementById('unit_id_' + id);
        let helper = document.getElementById('stok-helper-' + id);
        
        if (!stockInput || !unitSelect || !helper) return;

        let stock = parseFloat(stockInput.value) || 0;
        let selectedOption = unitSelect.options[unitSelect.selectedIndex];
        let isi = 1;
        let besarUnit = 'Ball';

        if (selectedOption && selectedOption.value) {
            isi = parseFloat(selectedOption.getAttribute('data-isi')) || 1;
            besarUnit = selectedOption.getAttribute('data-besar') || 'Ball';
        }

        if (stock > 0 && isi > 1) {
            let balls = stock / isi;
            let ballsFormatted = Number.isInteger(balls) ? balls : balls.toFixed(2).replace(/\.?0+$/, '');
            helper.textContent = `Setara dengan ${ballsFormatted} ${besarUnit}`;
        } else {
            helper.textContent = '';
        }
    }
</script>
