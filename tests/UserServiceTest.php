<?php

declare(strict_types=1);

namespace App\Tests;

use App\Model\User;
use App\Service\UserService;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $service;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new UserRepository();
        $this->service = new UserService($this->repository);
    }

    public function testGetAllUsers(): void
    {
        $users = $this->service->getAllUsers();
        $this->assertCount(5, $users);
    }

    public function testGetUserById(): void
    {
        $user = $this->service->getUserById(1);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Alice Johnson', $user->getName());
    }

    public function testGetUserByIdReturnsNullForInvalidId(): void
    {
        $user = $this->service->getUserById(999);
        $this->assertNull($user);
    }

    public function testCreateUser(): void
    {
        $user = $this->service->createUser([
            'name' => 'Frank Wilson',
            'email' => 'frank@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Frank Wilson', $user->getName());
        $this->assertEquals('frank@example.com', $user->getEmail());
        $this->assertEquals('user', $user->getRole());
    }

    public function testCreateUserWithDuplicateEmail(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already exists');

        $this->service->createUser([
            'name' => 'Another Alice',
            'email' => 'alice@example.com',
        ]);
    }

    public function testCreateUserWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createUser([
            'name' => 'Bad Email User',
            'email' => 'not-an-email',
        ]);
    }

    public function testCreateUserWithEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name is required');

        $this->service->createUser([
            'name' => '',
            'email' => 'test@example.com',
        ]);
    }

    public function testDeleteUser(): void
    {
        $result = $this->service->deleteUser(1);
        $this->assertTrue($result);
        $this->assertNull($this->service->getUserById(1));
    }

    public function testDeleteNonExistentUser(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service->deleteUser(999);
    }

    public function testGetAdmins(): void
    {
        $admins = $this->service->getAdmins();
        $this->assertCount(2, $admins);
    }

    public function testGetUserCount(): void
    {
        $this->assertEquals(5, $this->service->getUserCount());
    }
}
