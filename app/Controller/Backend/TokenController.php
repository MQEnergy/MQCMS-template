<?php
declare(strict_types=1);

namespace App\Controller\Backend;

use App\Amqp\Producer\DemoProducer;
use App\Utils\Common;
use App\Utils\Redis;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @AutoController()
 * Class TokenController
 * @package App\Controller\Admin
 */
class TokenController extends BaseController
{
    /**
     * 获取token信息
     * @return array|bool|object|string
     */
    public function index(RequestInterface $request)
    {
        return [
            'info' => $this->getTokenInfo(),
            'token' => $this->getAuthToken(),
            'uid' => $request->getAttribute('uid'),
            'uuid' => $request->getAttribute('uuid'),
            'current_action' => Common::getCurrentActionName($request, $this)
        ];
    }

    /**
     * 创建token
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(RequestInterface $request)
    {
        $token = $this->createAuthToken([
            'id' => 1,
        ], $request);
       Redis::getContainer()->set('admin:token:1', $token);

        return [
            'token' => $token,
            'jwt_config' => $this->getJwtConfig($request),
            'uid' => $request->getAttribute('uid'),
            'uuid' => $request->getAttribute('uuid')
        ];
    }
}