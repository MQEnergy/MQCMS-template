<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Exception\BusinessException;
use App\Utils\JWT;
use Hyperf\HttpMessage\Exception\UnauthorizedHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware extends BaseAuthMiddleware
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->challenge();
        $header = $request->getHeader($this->header);
        $tokenInfo = $this->authenticate($header);
        if (!$tokenInfo) {
            throw new UnauthorizedHttpException('Signature verification failed');
        }
        return $handler->handle($request);
    }

    /**
     * 验证token有效性并获取token值
     * @return array|bool|object|string|null
     */
    public function getAuthTokenInfo()
    {
        $config = self::getJwtConfig($this->request);
        try {
            self::$tokenInfo = JWT::getTokenInfo(self::$authToken, $config);
            return self::$tokenInfo;

        } catch (\Exception $e) {
            throw new BusinessException($e->getCode(), $e->getMessage());
        }
    }
}