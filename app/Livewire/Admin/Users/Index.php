<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Manajemen User — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;
    use WithPagination;

    public string $pageTitle = 'Manajemen User';

    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public string $search = '';

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:users,email',
        ];
        if ($this->editingId) {
            $rules['email'] = 'required|email|max:120|unique:users,email,'.$this->editingId;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    protected $validationAttributes = [
        'password' => 'Password',
        'passwordConfirmation' => 'Konfirmasi password',
    ];

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Manajemen User'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'passwordConfirmation']);
        $this->resetValidation();
    }

    public function edit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->password = '';
        $this->passwordConfirmation = '';
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'email' => $this->email];
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $u = User::findOrFail($this->editingId);
            $u->update($data);
            ActivityLog::record('User', 'update', 'Memperbarui admin "'.$this->name.'"');
            $this->dispatch('notify', type: 'success', message: 'Admin diperbarui.');
        } else {
            User::create($data);
            ActivityLog::record('User', 'create', 'Menambah admin "'.$this->name.'"');
            $this->dispatch('notify', type: 'success', message: 'Admin ditambahkan.');
        }
        $this->resetForm();
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'user') {
            if ($id === auth()->id()) {
                $this->dispatch('notify', type: 'error', message: 'Anda tidak dapat menghapus akun sendiri.');

                return;
            }
            User::findOrFail($id)->delete();
            ActivityLog::record('User', 'delete', 'Menghapus admin');
            $this->dispatch('notify', type: 'success', message: 'Admin dihapus.');
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")))
            ->latest()->paginate(10);

        return view('livewire.admin.users.index', ['users' => $users]);
    }
}
