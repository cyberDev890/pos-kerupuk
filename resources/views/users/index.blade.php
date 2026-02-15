@extends('layouts.app')
@section('content_title', 'Data User')
@section('content')
    <div class="card">
        <div class="d-flex justify-content-between p-2 border">
            <h4 class="h5"> Data Users</h4>
            <div>
                <x-user.form-user />
            </div>
        </div>
        <div class="card-body">
            <x-alert :errors="$errors" />
            <table class="table table-sm" id="table2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Email</th>
                        <th>Nama Users</th>
                        <th>Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->name }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <x-user.form-user :id="$user->id" />
                                    <a href="{{ route('users.destroy', $user->id) }}" class="btn btn-danger mx-1"
                                        data-confirm-delete="true">
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
@endsection
