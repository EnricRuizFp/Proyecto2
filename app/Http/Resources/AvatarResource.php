<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvatarResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            // Se obtiene la URL usando el método de Media Library definido en el modelo Avatar.
            'url'  => $this->getFirstMediaUrl('avatars') ?: asset('images/avatar-placeholder.jpg'),
        ];
    }
}
