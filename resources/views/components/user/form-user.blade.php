<div>
    <button type="button" class="btn {{ $id ? 'btn-warning' : 'btn-primary' }}" data-toggle="modal"
        data-target="#formUser{{ $id ?? '' }}">
        @if ($id)
            <i class="fas fa-edit"></i>
        @else
            User Baru
        @endif
    </button>

    <div class="modal fade" id="formUser{{ $id ?? '' }}">
        <form action="{{ route('users.index') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Form Edit Users' : 'Form Tambah Users' }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group my-1">
                            <label for=""> Email</label>
                            <input type="email" class="form-control" name="email" id="email"
                                value="{{ $id ? $email : old('email') }}">

                        </div>
                        <div class="form-group my-1">
                            <label for=""> nama</label>
                            <input type="name" class="form-control" name="name" id="name"
                                value="{{ $id ? $name : old('name') }}">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </form>
    </div>
</div>
