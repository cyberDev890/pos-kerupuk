@extends('layouts.app')
@section('content_title', 'Data Gaji Karyawan')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Data Gaji Karyawan</h3>
            <button type="button" class="btn btn-primary ml-auto" data-toggle="modal" data-target="#modal-create">
                <i class="fas fa-plus"></i> Tambah Gaji
            </button>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table2">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Nominal Gaji</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salaries as $key => $salary)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $salary->name }}</td>
                                <td>Rp. {{ number_format($salary->amount, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($salary->date)->format('d F Y') }}</td>
                                <td>{{ $salary->description ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#modal-edit-{{ $salary->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('budget.salary.destroy', $salary->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" data-confirm-delete="true">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="modal-edit-{{ $salary->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Edit Data Gaji</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('budget.salary.update', $salary->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Nama Karyawan</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ $salary->name }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nominal Gaji</label>
                                                    <input type="text" name="amount" class="form-control currency-input"
                                                        value="{{ number_format($salary->amount, 0, ',', '.') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal</label>
                                                    <input type="date" name="date" class="form-control"
                                                        value="{{ $salary->date }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Keterangan</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $salary->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $salaries->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modal-create">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Data Gaji</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('budget.salary.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Karyawan</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nominal Gaji</label>
                            <input type="text" name="amount" class="form-control currency-input" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Keterangan</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
