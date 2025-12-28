<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Response;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class CaptchaHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // 產生 6 碼隨機驗證碼
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        if ($session) {
            $session->set('captcha', $code);
        }

        // 建立圖片（改用 GD 內建字型，避免 TTF 缺檔）
        $im = imagecreatetruecolor(140, 44);
        $bg = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, 140, 44, $bg);

        // 簡單雜訊線
        for ($i = 0; $i < 5; $i++) {
            $noiseColor = imagecolorallocate($im, rand(150, 220), rand(150, 220), rand(150, 220));
            imageline($im, rand(0, 140), rand(0, 44), rand(0, 140), rand(0, 44), $noiseColor);
        }

        // 繪字：使用 GD 內建字型 5
        $textColor = imagecolorallocate($im, 30, 30, 30);
        $x = 12;
        $y = 12;
        for ($i = 0; $i < strlen($code); $i++) {
            imagestring($im, 5, $x, $y + rand(-2, 6), $code[$i], $textColor);
            $x += 22;
        }

        ob_start();
        imagepng($im);
        $imageData = ob_get_clean();
        imagedestroy($im);

        $stream = new Stream('php://memory', 'wb+');
        $stream->write($imageData);
        $stream->rewind();

        return new Response($stream, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
