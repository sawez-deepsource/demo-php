<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use App\Repository\UserRepository;

class UserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllUsers(): array
    {
        return $this->repository->findAll();
    }

    public function getUserById(int $id): ?User
    {
        return $this->repository->findById($id);
    }

    public function createUser(array $data): User
    {
        $this->validateUserData($data);

        $existing = $this->repository->findByEmail($data['email']);
        if ($existing !== null) {
            throw new \RuntimeException("User with email {$data['email']} already exists");
        }

        return $this->repository->createUser(
            $data['name'],
            $data['email'],
            $data['role'] ?? 'user'
        );
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new \RuntimeException("User with ID {$id} not found");
        }
        return $this->repository->delete($id);
    }

    public function getAdmins(): array
    {
        return $this->repository->findByRole('admin');
    }

    public function getUserCount(): int
    {
        return $this->repository->count();
    }

    private function validateUserData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }

        if (empty($data['email'])) {
            throw new \InvalidArgumentException('Email is required');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }

        if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
            throw new \InvalidArgumentException('Name must be between 2 and 100 characters');
        }
    }
}
