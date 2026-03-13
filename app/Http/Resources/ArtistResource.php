<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
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
            'description' => $this->when($this->description, $this->description),
            'birth_date' => $this->when($this->birth_date, $this->birth_date),
            'country' => $this->when($this->country, $this->country),
            'create_at' => $this->created_at->format('Y-m-d : H:m'),
            'update_at' => $this->updated_at->format('Y-m-d : H:m'),
            'songs' => SongResource::collection($this->whenLoaded('songs')),
        ];
    }
}
