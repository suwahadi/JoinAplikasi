<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Livewire\Component;

class ProfilePage extends Component
{
    public function render()
    {
        return view('livewire.pages.profile-page')
            ->layout('layouts.marketing', ['title' => 'Profil · ' . config('app.name')]);
    }
}
