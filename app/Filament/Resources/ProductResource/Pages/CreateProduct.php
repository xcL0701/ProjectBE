<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ProductPhoto;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
