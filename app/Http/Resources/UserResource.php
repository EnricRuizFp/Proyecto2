<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id'         => $this->id,
            'username'   => $this->username,
            'name'       => $this->name,
            'surname1'   => $this->surname1,
            'surname2'   => $this->surname2,
            'email'      => $this->email,
            'role_id'    => $this->roles,
            'roles'      => $this->roles,
            'avatar'     => $this->getFirstMediaUrl('users-avatars') ?: asset('images/placeholder.jpg'),
            'created_at' => $this->created_at->toDateString()
        ];
        

    }

}
