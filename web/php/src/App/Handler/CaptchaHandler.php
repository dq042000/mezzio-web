<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class CaptchaHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // 產生 6 碼隨機驗證碼
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $session = $request->getAttribute('session');
        if ($session) {
            $session->set('captcha', $code);
        }

        // 建立圖片
        $im = imagecreatetruecolor(120, 40);
        $bg = imagecolorallocate($im, 255, 255, 255);
        $text = imagecolorallocate($im, 0, 0, 0);
        imagefilledrectangle($im, 0, 0, 120, 40, $bg);
        imagettftext($im, 20, rand(-10,10), 15, 30, $text, __DIR__.'/../../data/font.ttf', $code);
        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);

        return new \Laminas\Diactoros\Response('php://memory', 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ], $imageData);
    }
}
