<?php

declare(strict_types=1);

namespace App\Model;

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $role;
    private ?string $createdAt;

    public function __construct(int $id, string $name, string $email, string $role = 'user', ?string $createdAt = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->createdAt,
        ];
    }
}
