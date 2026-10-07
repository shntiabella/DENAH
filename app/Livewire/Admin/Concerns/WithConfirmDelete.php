<?php

namespace App\Livewire\Admin\Concerns;

/**
 * Menyediakan state modal konfirmasi hapus yang reusable.
 * Komponen host memanggil confirmDelete('model', $id, 'Pesan?'),
 * lalu mengimplementasikan performDelete(string $model, int $id).
 */
trait WithConfirmDelete
{
    public bool $confirmingDeletion = false;
    public ?int $confirmingId = null;
    public string $confirmingModel = '';
    public string $confirmMessage = 'Hapus data ini? Tindakan tidak dapat dibatalkan.';

    public function confirmDelete(string $model, int $id, ?string $message = null): void
    {
        $this->confirmingModel = $model;
        $this->confirmingId = $id;
        $this->confirmMessage = $message ?? $this->confirmMessage;
        $this->confirmingDeletion = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeletion = false;
        $this->confirmingId = null;
        $this->confirmingModel = '';
    }

    public function confirmedDelete(): void
    {
        if ($this->confirmingId !== null && $this->confirmingModel !== '') {
            $this->performDelete($this->confirmingModel, $this->confirmingId);
        }
        $this->cancelDelete();
    }

    abstract public function performDelete(string $model, int $id): void;
}
