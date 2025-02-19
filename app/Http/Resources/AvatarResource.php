<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AvatarResource extends JsonResource
{
    public function toArray($request)
    {
        // Obtener la URL del media primero para asegurar que existe
        $mediaUrl = $this->getFirstMediaUrl('avatars');
        $defaultUrl = asset('images/avatar-placeholder.jpg');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'url' => $mediaUrl ?: $defaultUrl,
            'media_url' => $mediaUrl,  // URL directa del media
            'is_default' => empty($mediaUrl)  // Indicador si está usando la URL por defecto
        ];
    }
}
