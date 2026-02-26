@extends('layouts.app')
@section('content_title', 'Data Biaya Operasional')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Data Biaya Operasional</h3>
            <button type="button" class="btn btn-primary ml-auto" data-toggle="modal" data-target="#modal-create">
                <i class="fas fa-plus"></i> Tambah Biaya
            </button>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table2">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($operationalCosts as $key => $cost)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cost->description }}</td>
                                <td>Rp. {{ number_format($cost->amount, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($cost->date)->format('d F Y') }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#modal-edit-{{ $cost->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="{{ route('budget.operational.destroy', $cost->id) }}" class="btn btn-danger btn-sm"
                                        data-confirm-delete="true">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="modal-edit-{{ $cost->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title">Edit Biaya Operasional</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('budget.operational.update', $cost->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Keterangan</label>
                                                    <input type="text" name="description" class="form-control"
                                                        value="{{ $cost->description }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nominal</label>
                                                    <input type="text" name="amount" class="form-control currency-input"
                                                        value="{{ number_format($cost->amount, 0, ',', '.') }}" required inputmode="numeric">
                                                </div>
                                                <div class="form-group">
                                                    <label>Tanggal</label>
                                                    <input type="date" name="date" class="form-control"
                                                        value="{{ $cost->date }}" required>
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
                {{ $operationalCosts->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modal-create">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Biaya Operasional</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('budget.operational.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Keterangan</label>
                            <input type="text" name="description" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nominal</label>
                            <input type="text" name="amount" class="form-control currency-input" required inputmode="numeric">
                        </div>
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
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
