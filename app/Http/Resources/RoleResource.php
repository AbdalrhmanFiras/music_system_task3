<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'create_at' => $this->created_at->format('Y-m-d : H:m'),
            'update_at' => $this->updated_at->format('Y-m-d : H:m'),
        ];
    }
}
