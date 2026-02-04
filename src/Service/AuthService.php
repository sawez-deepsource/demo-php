<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\User;
use App\Repository\UserRepository;

class AuthService
{
    private UserRepository $repository;
    private array $sessions = [];

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function login(string $email, string $password): ?array
    {
        $user = $this->repository->findByEmail($email);

        if ($user === null) {
            return null;
        }

        $token = $this->generateToken($user);
        $this->sessions[$token] = $user->getId();

        return [
            'token' => $token,
            'user' => $user->toArray(),
        ];
    }

    public function logout(string $token): bool
    {
        if (isset($this->sessions[$token])) {
            unset($this->sessions[$token]);
            return true;
        }
        return false;
    }

    public function validateToken(string $token): ?User
    {
        if (!isset($this->sessions[$token])) {
            return null;
        }

        $userId = $this->sessions[$token];
        return $this->repository->findById($userId);
    }

    public function isAuthenticated(string $token): bool
    {
        return isset($this->sessions[$token]);
    }

    private function generateToken(User $user): string
    {
        return hash('sha256', sprintf('%d-%s-%d', $user->getId(), $user->getEmail(), time()));
    }
}
