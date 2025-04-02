<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvatarResource extends JsonResource
{
    public function toArray($request)
    {
        $mediaUrl = $this->getFirstMediaUrl('avatars');
        $defaultUrl = asset('images/avatar-placeholder.jpg');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'image_route' => $mediaUrl ?: $defaultUrl,  // Para mantener compatibilidad con la vista
            'url' => $mediaUrl ?: $defaultUrl,          // URL completa de la imagen
            'is_default' => empty($mediaUrl)
        ];
    }
}
