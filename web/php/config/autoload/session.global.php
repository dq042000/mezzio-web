<?php

declare(strict_types=1);

$redisHost = $_ENV['REDIS_SESSION_HOST'] ?? 'mezzio-redis';
$redisPort = $_ENV['REDIS_SESSION_PORT'] ?? '6379';
$redisPassword = $_ENV['REDIS_PASSWORD'] ?? '';

$savePath = sprintf('tcp://%s:%s', $redisHost, $redisPort);

if ($redisPassword !== '') {
    $savePath .= sprintf('?auth=%s', $redisPassword);
}

ini_set('session.save_handler', 'redis');
ini_set('session.save_path', $savePath);

return [
    'session' => [
        'name' => 'MEZZIOSESSID',
        'cookie_httponly' => true,
        'cookie_secure' => false,
        'cookie_samesite' => 'Lax',
        'gc_maxlifetime' => 1440,
    ],
];