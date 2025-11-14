<?php

namespace App\View\Components\User;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\User;

class FormUser extends Component
{
    /**
     * Create a new component instance.
     */
    public $id, $name, $email;
    public function __construct($id = null)
    {
        if ($id) {
            $user = User::find($id);
            $this->id = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
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
