<?php

declare(strict_types=1);

$authorizationInterface = 'Mezzio\Authorization\AuthorizationInterface';
$aclFactory = 'Mezzio\Authorization\Acl\AclFactory';

if (!class_exists($aclFactory)) {
    return [
        'authorization' => [
            'roles' => [
                'guest' => [],
                'user' => ['guest'],
                'system_admin' => ['user'],
                'developer_admin' => ['system_admin'],
            ],
            'resources' => [],
            'allow' => [],
        ],
    ];
}

return [
    'dependencies' => [
        'factories' => [
            $authorizationInterface => $aclFactory,
        ],
    ],
    'authorization' => [
        'roles' => [
            'guest' => [],
            'user' => ['guest'],
            'system_admin' => ['user'],
            'developer_admin' => ['system_admin'],
        ],
        'resources' => [
            'home',
            'api.ping',
            'login.form',
            'login.submit',
            'captcha',
        ],
        'allow' => [
            'guest' => [
                'login.form' => ['GET'],
                'login.submit' => ['POST'],
                'captcha' => ['GET'],
            ],
            'user' => [
                'home' => ['GET'],
            ],
            'system_admin' => [
                'home' => ['GET'],
            ],
            'developer_admin' => [
                'home' => ['GET'],
                'api.ping' => ['GET'],
            ],
        ],
    ],
];