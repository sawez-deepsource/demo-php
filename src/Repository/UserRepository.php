<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

class UserRepository
{
    private array $users = [];
    private int $nextId = 1;

    public function __construct()
    {
        $this->seed();
    }

    private function seed(): void
    {
        $this->save(new User($this->nextId++, 'Alice Johnson', 'alice@example.com', 'admin'));
        $this->save(new User($this->nextId++, 'Bob Smith', 'bob@example.com'));
        $this->save(new User($this->nextId++, 'Charlie Brown', 'charlie@example.com'));
        $this->save(new User($this->nextId++, 'Diana Prince', 'diana@example.com', 'admin'));
        $this->save(new User($this->nextId++, 'Eve Davis', 'eve@example.com'));
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function findByRole(string $role): array
    {
        return array_filter($this->users, fn(User $user) => $user->getRole() === $role);
    }

    public function save(User $user): void
    {
        $this->users[$user->getId()] = $user;
    }

    public function delete(int $id): bool
    {
        if (isset($this->users[$id])) {
            unset($this->users[$id]);
            return true;
        }
        return false;
    }

    public function count(): int
    {
        return count($this->users);
    }

    public function createUser(string $name, string $email, string $role = 'user'): User
    {
        $user = new User($this->nextId++, $name, $email, $role);
        $this->save($user);
        return $user;
    }
}
