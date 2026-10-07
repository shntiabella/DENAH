<?php

namespace App\Livewire\Admin\Auth;

use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.admin-guest')]
#[Title('Lupa Password — DENAH CMS')]
class ForgotPassword extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public bool $sent = false;

    public function sendResetLink(): void
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            event(new PasswordResetLinkSent($this->email));
            $this->sent = true;
            $this->dispatch('notify', type: 'success', message: 'Tautan reset password telah dikirim ke email Anda.');

            return;
        }

        $this->addError('email', 'Email tidak ditemukan dalam sistem.');
    }

    public function render()
    {
        return view('livewire.admin.auth.forgot-password');
    }
}
