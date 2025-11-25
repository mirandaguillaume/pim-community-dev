<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

// Ensure test env/debug for phpunit
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? '1';

require \dirname(__DIR__) . '/config/bootstrap.php';

// Bootstrap a kernel to preload a security token for integration tests needing it
$kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

if ($container->has('security.token_storage')) {
    $tokenStorage = $container->get('security.token_storage');
    if (null === $tokenStorage->getToken()) {
        $userRepository = $container->has('pim_user.repository.user') ? $container->get('pim_user.repository.user') : null;
        $user = $userRepository?->findOneBy(['username' => 'admin']) ?? $userRepository?->findOneBy([]);
        if (null !== $user) {
            $token = new UsernamePasswordToken($user, 'test', 'main', $user->getRoles());
            $tokenStorage->setToken($token);
        }
    }
}

$kernel->shutdown();
