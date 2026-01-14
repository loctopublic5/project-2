<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'user_info' => [
            'id' => $this->resource['user']->id,
            'name' => $this->resource['user']->full_name, // Mapping cột full_name 
            'email' => $this->resource['user']->email,
            'roles' => $this->resource['roles'], // Danh sách roles
            'last_login_at' => $this->resource['user']->last_login_at?->format('d/m/Y H:i:s'),
        ],
        'authorization' => [
            'token' => $this->resource['access_token'],
            'type' => 'Bearer',
        ],
    ];
    }
}
