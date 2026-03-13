<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'cover' => $this->cover,
            'file_path' => $this->file_path,

            'create_at' => $this->created_at->format('Y-m-d : H:m'),
            'update_at' => $this->updated_at->format('Y-m-d : H:m'),

            'artists' => ArtistResource::collection($this->whenLoaded('artists')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),

        ];
    }
}
