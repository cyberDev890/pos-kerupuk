<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    //

    public function index()
    {
        $users = User::all();
        confirmDelete('Hapus User', 'Apakah anda yakin menghapus user ini?');
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $id = $request->id;
        $request->validate(
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.unique' => 'Email sudah digunakan.',
            ]
        );
        $newRequest = $request->all();
        if (!$id) {
            $newRequest['password'] = Hash::make('123456');
        }
        User::updateOrCreate(['id' => $id], $newRequest);
        toast('Data user berhasil disimpan.', 'success');
        return redirect()->route('users.index');
    }

    public function gantiPassword(Request $request)
    {

        $request->validate(
            [
                'old_password' => 'required',
                'password' => 'required|min:6|confirmed',
                // 'password' => Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            [
                'old_password.required' => 'Password lama wajib diisi.',
                'password.required' => 'Password baru wajib diisi.',
                'password.min' => 'Password minimal 6 karakter.',
                'password.confirmed' => 'Password baru tidak cocok.',
            ]
        );
        /** @var \App\Models\User $user */
        $user = User::find(Auth::id());

        if (!$user) {
            toast()->error('User tidak ditemukan.');
            return redirect()->route('dashboard');
        }

        if (!Hash::check($request->old_password, $user->password)) {
            toast()->error('Password lama tidak sesuai.');
            return redirect()->route('dashboard');
        }
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        toast()->success('Password berhasil diubah.');
        return redirect()->route('dashboard');
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (Auth::id() == $user->id) {
            toast()->error('Tidak dapat menghapus user yang sedang login.');
            return redirect()->route('users.index');
        }
        $user->delete();
        toast()->success('Data user berhasil dihapus.');
        return redirect()->route('users.index');
    }
}
