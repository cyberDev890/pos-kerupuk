@extends('layouts.app')

@section('content_title', 'Daftar Piutang Pelanggan')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Piutang Pelanggan</h3>
    </div>
    <div class="card-body">
        <x-alert :errors="$errors" />
        
        <table class="table table-bordered table-striped" id="table2">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Telepon</th>
                    <th>Total Hutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customers as $index => $customer)
                @php
                    $totalHutang = $customer->transactions->sum('remaining_debt');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $customer->nama }}</td>
                    <td>{{ $customer->telepon ?? '-' }}</td>
                    <td class="text-danger font-weight-bold">Rp {{ number_format($totalHutang, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('receivable.show', $customer->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Detail / Bayar
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
