<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\AuthService;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        $repository = new UserRepository();
        $this->authService = new AuthService($repository);
    }

    public function testLoginWithValidUser(): void
    {
        $result = $this->authService->login('alice@example.com', 'password');
        $this->assertNotNull($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('Alice Johnson', $result['user']['name']);
    }

    public function testLoginWithInvalidEmail(): void
    {
        $result = $this->authService->login('nobody@example.com', 'password');
        $this->assertNull($result);
    }

    public function testValidateToken(): void
    {
        $loginResult = $this->authService->login('alice@example.com', 'password');
        $token = $loginResult['token'];

        $user = $this->authService->validateToken($token);
        $this->assertNotNull($user);
        $this->assertEquals('Alice Johnson', $user->getName());
    }

    public function testValidateInvalidToken(): void
    {
        $user = $this->authService->validateToken('invalid-token');
        $this->assertNull($user);
    }

    public function testIsAuthenticated(): void
    {
        $loginResult = $this->authService->login('bob@example.com', 'password');
        $token = $loginResult['token'];

        $this->assertTrue($this->authService->isAuthenticated($token));
        $this->assertFalse($this->authService->isAuthenticated('fake-token'));
    }

    public function testLogout(): void
    {
        $loginResult = $this->authService->login('alice@example.com', 'password');
        $token = $loginResult['token'];

        $this->assertTrue($this->authService->isAuthenticated($token));

        $result = $this->authService->logout($token);
        $this->assertTrue($result);
        $this->assertFalse($this->authService->isAuthenticated($token));
    }

    public function testLogoutWithInvalidToken(): void
    {
        $result = $this->authService->logout('nonexistent-token');
        $this->assertFalse($result);
    }
}
