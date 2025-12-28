<?php

declare(strict_types=1);

namespace Login\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Base\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Login\Traits\LoginAuthTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Csrf\CsrfGuardFactoryInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;

class LoginHandler implements RequestHandlerInterface
{
    use LoginAuthTrait;

    private TemplateRendererInterface $renderer;
    private CsrfGuardFactoryInterface $csrfGuardFactory;
    private EntityManagerInterface $entityManager;

    public function __construct(
        TemplateRendererInterface $renderer,
        CsrfGuardFactoryInterface $csrfGuardFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->renderer = $renderer;
        $this->csrfGuardFactory = $csrfGuardFactory;
        $this->entityManager = $entityManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $error = '';
        $guard = $this->csrfGuardFactory->createGuardFromRequest($request);
        $session = $this->getSession($request);

        if (!$session instanceof SessionInterface) {
            return new HtmlResponse($this->renderer->render('auth::login', [
                'csrf' => $guard->generateToken(),
                'error' => 'Session 無法使用，請稍後再試',
            ]));
        }

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody() ?? [];
            $csrf = $data['csrf'] ?? '';
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';
            $captcha = $data['captcha'] ?? '';

            // 驗證 CSRF
            if (!$guard->validateToken($csrf)) {
                $error = 'CSRF 驗證失敗，請重新整理頁面';
            } elseif (!$this->validateCaptcha($request, $captcha)) {
                $error = '驗證碼錯誤';
            } elseif (empty($username) || empty($password)) {
                $error = '帳號或密碼不得為空';
            } else {
                $userPayload = $this->authenticate($username, $password);

                if ($userPayload !== null) {
                    $session->regenerate();
                    $session->set('user', [
                        'current_user' => $userPayload,
                    ]);

                    return new RedirectResponse('/');
                }

                $error = '帳號或密碼錯誤';
            }
        }

        $csrfToken = $guard->generateToken();
        return new HtmlResponse($this->renderer->render('auth::login', [
            'csrf' => $csrfToken,
            'error' => $error,
        ]));
    }
}
