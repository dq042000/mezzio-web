<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Csrf\CsrfGuardFactoryInterface;
use Psr\Container\ContainerInterface;

class LoginHandler implements RequestHandlerInterface
{
    private TemplateRendererInterface $renderer;
    private CsrfGuardFactoryInterface $csrfGuardFactory;

    public function __construct(TemplateRendererInterface $renderer, CsrfGuardFactoryInterface $csrfGuardFactory)
    {
        $this->renderer = $renderer;
        $this->csrfGuardFactory = $csrfGuardFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $error = '';
        $guard = $this->csrfGuardFactory->createGuardFromRequest($request);

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
                // TODO: 實作帳號密碼驗證
                // if (auth success) {
                //     return new RedirectResponse('/dashboard');
                // }
                $error = '帳號或密碼錯誤';
            }
        }

        $csrfToken = $guard->generateToken();
        return new HtmlResponse($this->renderer->render('auth::login', [
            'csrf' => $csrfToken,
            'error' => $error,
        ]));
    }

    private function validateCaptcha(ServerRequestInterface $request, string $input): bool
    {
        $session = $request->getAttribute('session');
        if (!$session) {
            return false;
        }
        $expected = $session->get('captcha');
        return $expected && strtolower($input) === strtolower($expected);
    }
}
