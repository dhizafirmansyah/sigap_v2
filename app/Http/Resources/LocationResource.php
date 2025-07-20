<?php

namespace App\Http\Resources;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Location
 */
class LocationResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'address' => $this->address,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'employees_count' => $this->employees_count ?? $this->employees()->count(),
            'packs_count' => $this->packs_count ?? $this->packs()->count(),
            'kemas_count' => $this->kemas_count ?? $this->kemas()->count(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'formatted_created_at' => $this->created_at?->format('d M Y H:i'),
            'formatted_updated_at' => $this->updated_at?->format('d M Y H:i'),
        ];
    }
}
