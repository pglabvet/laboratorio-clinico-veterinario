<?php

namespace App\Livewire;

use Livewire\Component;

class AnalisisListado extends Component
{
    public $busqueda = '';

    public function render()
    {
        return view('livewire.analisis-listado');
    }
}
