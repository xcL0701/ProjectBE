<?php

namespace App\Filament\Resources\MachineResource\Pages;

use App\Filament\Resources\MachineResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMachine extends CreateRecord
{
    protected static string $resource = MachineResource::class;

    public function getTitle(): string
    {
        return 'Tambah Kategori Mesin Baru';
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
