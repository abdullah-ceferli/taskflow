<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->getRoleNames()->values(),
            'workspaces' => $this->workspaces->map(fn ($workspace): array => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'role' => $workspace->pivot->role,
            ])->values(),
        ];
    }
}
