<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\UserController;
use App\Service\UserService;
use App\Repository\UserRepository;

$repository = new UserRepository();
$service = new UserService($repository);
$controller = new UserController($service);

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'list':
        echo $controller->listUsers();
        break;
    case 'show':
        echo $controller->showUser((int) $id);
        break;
    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        echo $controller->createUser($data);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
}
