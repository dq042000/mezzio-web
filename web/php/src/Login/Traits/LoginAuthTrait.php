<?php

declare(strict_types=1);

namespace Login\Traits;

use Base\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;

trait LoginAuthTrait
{
    private function authenticate(string $username, string $password): ?array
    {
        if (!$this->entityManager instanceof EntityManagerInterface) {
            return null;
        }

        /** @var Users|null $user */
        $user = $this->entityManager
            ->getRepository(Users::class)
            ->findOneBy(['username' => $username]);

        if (!$user instanceof Users) {
            return null;
        }

        if (!$this->verifyPassword($password, (string) $user->getPassword())) {
            return null;
        }

        $roleName = $user->getAuthRole() ? (string) $user->getAuthRole()->getName() : '一般使用者';
        $aclRole = $this->mapRoleNameToAclRole($roleName);

        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'role' => [
                'id' => $user->getAuthRoleId(),
                'name' => $roleName,
                'acl_role' => $aclRole,
            ],
        ];
    }

    private function verifyPassword(string $inputPassword, string $storedHash): bool
    {
        if ($storedHash === '') {
            return false;
        }

        if (password_get_info($storedHash)['algo'] !== 0) {
            return password_verify($inputPassword, $storedHash);
        }

        return hash_equals($storedHash, $inputPassword);
    }

    private function mapRoleNameToAclRole(string $roleName): string
    {
        $mapping = [
            '開發端系統管理員' => 'developer_admin',
            '系統管理員' => 'system_admin',
            '一般使用者' => 'user',
        ];

        return $mapping[$roleName] ?? 'user';
    }

    private function getSession(ServerRequestInterface $request): ?SessionInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        return $session instanceof SessionInterface ? $session : null;
    }

    private function validateCaptcha(ServerRequestInterface $request, string $input): bool
    {
        $session = $this->getSession($request);
        if (!$session instanceof SessionInterface) {
            return false;
        }

        $expected = (string) $session->get('captcha', '');

        return $expected !== '' && strtolower($input) === strtolower($expected);
    }
}