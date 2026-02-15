@extends('layouts.app')
@section('content_title', 'Data Satuan')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Data Satuan</h4>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#formUnit">
                Satuan Baru
            </button>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <table class="table table-sm" id="table2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Satuan</th>
                        <th>Satuan Kecil</th>
                        <th>Satuan Besar</th>
                        <th>Isi / Konversi</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $index => $unit)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $unit->nama_satuan }}</td>
                            <td>{{ $unit->satuan_kecil }}</td>
                            <td>{{ $unit->satuan_besar }}</td>
                            <td>1 {{ $unit->satuan_besar }} = {{ $unit->isi }} {{ $unit->satuan_kecil }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-warning mx-1 btn-edit" 
                                        data-id="{{ $unit->id }}"
                                        data-nama="{{ $unit->nama_satuan }}"
                                        data-kecil="{{ $unit->satuan_kecil }}"
                                        data-besar="{{ $unit->satuan_besar }}"
                                        data-isi="{{ $unit->isi }}"
                                        data-toggle="modal" data-target="#formUnit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="{{ route('master-data.unit.destroy', $unit->id) }}"
                                        class="btn btn-danger mx-1" data-confirm-delete="true">
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

    <!-- Modal Form -->
    <div class="modal fade" id="formUnit">
        <form action="{{ route('master-data.unit.store') }}" method="POST" id="formUnitAction">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalTitle">Form Tambah Satuan</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Set Satuan</label>
                            <input type="text" class="form-control" name="nama_satuan" id="nama_satuan" placeholder="Contoh: Set Kerupuk Kaleng" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan Kecil</label>
                                    <input type="text" class="form-control" name="satuan_kecil" id="satuan_kecil" placeholder="Contoh: Pcs" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan Besar</label>
                                    <input type="text" class="form-control" name="satuan_besar" id="satuan_besar" placeholder="Contoh: Bal" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Isi (Konversi)</label>
                            <input type="number" class="form-control" name="isi" id="isi" placeholder="1 Satuan Besar = ? Satuan Kecil" required>
                            <small class="text-muted">Masukkan jumlah satuan kecil dalam 1 satuan besar.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @section('scripts')
    <script>
        $(document).ready(function() {
            $(document).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let kecil = $(this).data('kecil');
                let besar = $(this).data('besar');
                let isi = $(this).data('isi');

                $('#modalTitle').text('Edit Satuan');
                $('#formUnitAction').attr('action', '/master-data/unit/' + id);
                $('#methodField').val('PUT');
                
                $('#nama_satuan').val(nama);
                $('#satuan_kecil').val(kecil);
                $('#satuan_besar').val(besar);
                $('#isi').val(isi);
            });

            $('#formUnit').on('hidden.bs.modal', function () {
                $('#modalTitle').text('Tambah Satuan');
                $('#formUnitAction').attr('action', '{{ route("master-data.unit.store") }}');
                $('#methodField').val('POST');
                $('#formUnitAction')[0].reset();
            });
        });
    </script>
    @endsection

@endsection
