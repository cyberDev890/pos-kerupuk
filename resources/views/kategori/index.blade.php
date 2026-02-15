@extends('layouts.app')
@section('content_title', 'Data Kategori')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Data Kategori</h4>
            <div>
                <x-kategori.form-kategori />
            </div>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <table class="table table-sm " id="table2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kategori as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->nama_kategori }}</td>
                            <td>{{ $item->deskripsi }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <x-kategori.form-kategori :id="$item->id" />
                                    <a href="{{ route('master-data.kategori.destroy', $item->id) }}"
                                        data-confirm-delete="true" class="btn btn-danger mx-1">
                                        <i class="fa fa-trash"></i>

                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
