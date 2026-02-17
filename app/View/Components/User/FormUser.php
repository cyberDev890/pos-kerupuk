<?php

namespace App\View\Components\User;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormUser extends Component
{
    /**
     * Create a new component instance.
     */
    public $id, $name, $email, $role, $permissions;
    public function __construct($id = null)
    {
        if ($id) {
            $user = User::find($id);
            if ($user) {
                $this->id = $user->id;
                $this->name = $user->name;
                $this->email = $user->email;
                $this->role = $user->role;
                $this->permissions = $user->permissions ?? [];
            }
        } else {
            $this->role = 'karyawan';
            $this->permissions = [];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.user.form-user');
    }
}
