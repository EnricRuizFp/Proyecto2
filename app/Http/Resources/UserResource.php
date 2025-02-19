<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        try {
            $currentAvatar = $this->avatares()->latest()->first();
            $avatarUrl = $currentAvatar ? $currentAvatar->getFirstMediaUrl('avatars') : asset('images/placeholder.jpg');

            \Log::debug('[UserResource] Generando recurso de usuario', [
                'user_id' => $this->id,
                'avatar_url' => $avatarUrl
            ]);

            return [
                'id'         => $this->id,
                'username'   => $this->username,
                'name'       => $this->name,
                'surname1'   => $this->surname1,
                'surname2'   => $this->surname2,
                'email'      => $this->email,
                'role_id'    => $this->roles,
                'roles'      => $this->roles,
                'avatar'     => $avatarUrl,
                'created_at' => $this->created_at->toDateString()
            ];
        } catch (\Exception $e) {
            \Log::error('[UserResource] Error: ' . $e->getMessage());
            return [];
        }
    }
}
