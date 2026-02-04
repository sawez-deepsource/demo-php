<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function listUsers(): string
    {
        $users = $this->userService->getAllUsers();
        $result = array_map(fn($user) => $user->toArray(), $users);

        return $this->jsonResponse($result);
    }

    public function showUser(int $id): string
    {
        $user = $this->userService->getUserById($id);

        if ($user === null) {
            http_response_code(404);
            return $this->jsonResponse(['error' => 'User not found']);
        }

        return $this->jsonResponse($user->toArray());
    }

    public function createUser(array $data): string
    {
        try {
            $user = $this->userService->createUser($data);
            http_response_code(201);
            return $this->jsonResponse($user->toArray());
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            return $this->jsonResponse(['error' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            http_response_code(409);
            return $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function deleteUser(int $id): string
    {
        try {
            $this->userService->deleteUser($id);
            return $this->jsonResponse(['message' => 'User deleted']);
        } catch (\RuntimeException $e) {
            http_response_code(404);
            return $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    private function jsonResponse(array $data): string
    {
        header('Content-Type: application/json');
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
