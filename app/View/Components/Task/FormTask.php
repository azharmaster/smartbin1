<?php

namespace App\View\Components\Task;

use App\Models\Task;
use App\Models\User;
use App\Models\Asset;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormTask extends Component
{
    public $id, $asset_id, $user_id, $description, $users, $assets;

    public function __construct($id = null)
    {
        $this->users = User::all();
        $this->assets = Asset::all();

        if ($id) {
            $task = Task::find($id);
            $this->id = $task->id;
            $this->asset_id = $task->asset_id;   // updated
            $this->user_id = $task->user_id;     // updated
            $this->description = $task->description;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.task.form-task');
    }
}
