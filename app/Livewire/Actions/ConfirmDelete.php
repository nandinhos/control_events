<?php

namespace App\Livewire\Actions;

use Livewire\Component;

class ConfirmDelete extends Component
{
    public string $model;
    public string $dispatchEvent;

    public function render()
    {
        return view('livewire.actions.confirm-delete');
    }
}
