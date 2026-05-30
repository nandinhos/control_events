<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Role constants based on business rules (RBAC)
     */
    public const ROLE_CONTRACT = 'role_contract';
    public const ROLE_RECEIVABLE = 'role_receivable';
    public const ROLE_PAYABLE = 'role_payable';
    public const ROLE_INTERNATIONAL = 'role_international';
    public const ROLE_ADMIN_FINANCE = 'role_admin_finance';

    /**
     * Valid roles
     */
    public const VALID_ROLES = [
        self::ROLE_CONTRACT,
        self::ROLE_RECEIVABLE,
        self::ROLE_PAYABLE,
        self::ROLE_INTERNATIONAL,
        self::ROLE_ADMIN_FINANCE,
    ];

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? []);
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($roles, $this->roles ?? []));
    }

    /**
     * Check if user has all of the given roles
     */
    public function hasAllRoles(array $roles): bool
    {
        return empty(array_diff($roles, $this->roles ?? []));
    }

    /**
     * Check if user is admin finance (full access)
     */
    public function isAdminFinance(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN_FINANCE);
    }

    /**
     * Check if user can manage contracts only
     */
    public function isContractOnly(): bool
    {
        return $this->hasRole(self::ROLE_CONTRACT) 
            && !$this->hasAnyRole([self::ROLE_RECEIVABLE, self::ROLE_PAYABLE, self::ROLE_ADMIN_FINANCE]);
    }
}