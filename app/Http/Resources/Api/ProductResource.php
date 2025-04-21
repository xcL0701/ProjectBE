<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_barang' => $this->kode_barang,
            'slug' => $this->slug,
            'name' => $this->name,
            'photo' => $this->photo,
            'model_3d' => $this->model_3d,
            'desc' => $this->desc,
            'machine_type' => $this->machine_type,
            'sparepart_type' => $this->sparepart_type,
            'brand' => $this->brand,
            'stock' => $this->stock,
        ];
    }
}
