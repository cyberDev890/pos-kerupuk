@extends('layouts.app')
@section('content_title', 'Data Pelanggan')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Data Pelanggan</h4>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#formCustomer">
                Pelanggan Baru
            </button>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="table2">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pelanggan</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Keterangan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $index => $customer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $customer->nama }}</td>
                                <td>{{ $customer->telepon }}</td>
                                <td>{{ $customer->alamat }}</td>
                                <td>{{ $customer->keterangan }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <button class="btn btn-warning mx-1 btn-edit" 
                                            data-id="{{ $customer->id }}"
                                            data-nama="{{ $customer->nama }}"
                                            data-telepon="{{ $customer->telepon }}"
                                            data-alamat="{{ $customer->alamat }}"
                                            data-keterangan="{{ $customer->keterangan }}"
                                            data-toggle="modal" data-target="#formCustomer">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="{{ route('master-data.customer.show', $customer->id) }}" class="btn btn-info mx-1" title="Detail & Harga Khusus">
                                            <i class="fas fa-tags"></i>
                                        </a>
                                        <a href="{{ route('master-data.customer.destroy', $customer->id) }}"
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
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="formCustomer">
        <form action="{{ route('master-data.customer.store') }}" method="POST" id="formCustomerAction">
            @csrf
            <input type="hidden" name="_method" id="methodField" value="POST">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalTitle">Form Tambah Pelanggan</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Pelanggan</label>
                            <input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Pelanggan" required>
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="text" class="form-control" name="telepon" id="telepon" placeholder="No Telepon">
                        </div>
                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea class="form-control" name="alamat" id="alamat" rows="3" placeholder="Alamat Pelanggan"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="2" placeholder="Catatan Tambahan"></textarea>
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
            // Edit Customer
            $(document).on('click', '.btn-edit', function() {
                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let telepon = $(this).data('telepon');
                let alamat = $(this).data('alamat');
                let keterangan = $(this).data('keterangan');

                $('#modalTitle').text('Edit Pelanggan');
                $('#formCustomerAction').attr('action', '/master-data/customer/' + id);
                $('#methodField').val('PUT');
                
                $('#nama').val(nama);
                $('#telepon').val(telepon);
                $('#alamat').val(alamat);
                $('#keterangan').val(keterangan);
            });

            $('#formCustomer').on('hidden.bs.modal', function () {
                $('#modalTitle').text('Tambah Pelanggan');
                $('#formCustomerAction').attr('action', '{{ route("master-data.customer.store") }}');
                $('#methodField').val('POST');
                $('#formCustomerAction')[0].reset();
            });
        });
    </script>
    @endsection

@endsection
