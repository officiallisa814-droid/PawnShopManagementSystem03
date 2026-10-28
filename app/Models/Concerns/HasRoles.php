<?php

namespace App\Models\Concerns;

trait HasRoles
{
    /** គណនីទើបចុះឈ្មោះ កំពុងរង់ចាំ Admin អនុម័ត */
    public function isPending(): bool
    {
        return ($this->role ?? '') === 'pending';
    }

    /** Role ដែលត្រឹមត្រូវ (Role ចាស់ដូចជា "staff" ធ្លាក់ទៅ default = cashier) */
    public function roleKey(): string
    {
        $role = $this->role ?? config('roles.default');

        if ($role === 'pending') {
            return 'pending';
        }

        return array_key_exists($role, config('roles.roles'))
            ? $role
            : config('roles.default');
    }

    public function roleConfig(): array
    {
        return $this->isPending()
            ? config('roles.pending')
            : config('roles.roles.' . $this->roleKey());
    }

    public function roleLabel(): string
    {
        return $this->roleConfig()['title'];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->roleKey(), $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->roleKey() === 'admin';
    }

    public function hasPermission(string $permission): bool
    {
        $granted = $this->roleConfig()['permissions'];

        return in_array('*', $granted, true) || in_array($permission, $granted, true);
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }
}