@extends('layouts.app')

@section('content_title', 'Piutang Pelanggan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold text-muted">
                    <i class="fas fa-hand-holding-usd mr-1 text-primary"></i> Daftar Piutang Pelanggan
                </h3>
            </div>
            <div class="card-body">
                <x-alert :errors="$errors" />
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table1">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 border-top-0 pl-4" style="width: 50px">No</th>
                                <th class="py-3 border-top-0">Nama Pelanggan</th>
                                <th class="py-3 border-top-0 text-center">Telepon</th>
                                <th class="py-3 border-top-0 text-right">Total Sisa Piutang</th>
                                <th class="py-3 border-top-0 text-center pr-4" style="width: 150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $index => $customer)
                            @php
                                $totalHutang = $customer->transactions->sum('remaining_debt');
                            @endphp
                            <tr>
                                <td class="pl-4 text-muted small">{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ $customer->nama }}</span>
                                    @if($customer->alamat)
                                        <small class="text-muted d-block italic">{{ $customer->alamat }}</small>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold text-muted small">{{ $customer->telepon ?? '-' }}</td>
                                <td class="text-right font-weight-bold text-danger">Rp {{ number_format($totalHutang, 0, ',', '.') }}</td>
                                <td class="text-center pr-4">
                                    <a href="{{ route('receivable.show', $customer->id) }}" class="btn btn-sm btn-outline-info px-3 shadow-xs font-weight-bold">
                                        <i class="fas fa-eye mr-1"></i> LIHAT DETAIL
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .italic { font-style: italic; }
</style>
@endsection
