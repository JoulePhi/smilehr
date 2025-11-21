<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'total_roles' => $this->resource['total_roles'] ?? 0,
            'total_permissions' => $this->resource['total_permissions'] ?? 0,
            'total_users' => $this->resource['total_users'] ?? 0,
            'active_mappings' => $this->resource['active_mappings'] ?? 0,
            'roles_by_guard' => $this->resource['roles_by_guard'] ?? [],
            'recent_roles' => $this->when(isset($this->resource['recent_roles']), function () {
                return $this->resource['recent_roles']->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions_count' => $role->permissions_count,
                        'users_count' => $role->users_count,
                        'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),
        ];
    }
}