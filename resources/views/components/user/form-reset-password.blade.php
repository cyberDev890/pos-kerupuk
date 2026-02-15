<div>

    <!-- Modal -->
    <div class="modal fade" id="formResetPassword" tabindex="-1" aria-labelledby="formResetPasswordLabel"
        aria-hidden="true">
        <form action="{{ route('users.Reset-password') }}" method="POST">
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formResetPasswordLabel">Form Reset Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group my-1">
                            <label for="">Password Lama</label>
                            <input type="password"name="old_password" class="form-control" id="old_password">
                            @error('old_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group my-1">
                            <label for="">Password Baru</label>
                            <input type="password"name="password" class="form-control" id="password">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group my-1">
                            <label for="">Konfirmasi Password Baru</label>
                            <input type="password"name="password_confirmation" class="form-control"
                                id="password_confirmation">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
