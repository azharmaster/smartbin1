<?php

namespace App\View\Components\User;

use Illuminate\View\Component;
use App\Models\User;

class FormUser extends Component
{
    public $id;
    public $name;
    public $email;
    public $phone;
    public $role;

    /**
     * Create a new component instance.
     *
     * @param int|null $id
     * @param string|null $name
     * @param string|null $email
     * @param string|null $phone
     * @param int|null $role
     */
    public function __construct($id = null, $name = null, $email = null, $phone = null, $role = null)
    {
        if ($id) {
            // Editing existing user
            $user = User::find($id);
            if ($user) {
                $this->id = $user->id;
                $this->name = $user->name;
                $this->email = $user->email;
                $this->phone = $user->phone;
                $this->role = $user->role;
            }
        } else {
            // Creating new user
            $this->id = null;
            $this->name = $name ?? '';
            $this->email = $email ?? '';
            $this->phone = $phone ?? '';
            $this->role = $role ?? 1; // Default to Admin
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        // Return the Blade file for this component
        return view('components.user.form-user');
    }
}
