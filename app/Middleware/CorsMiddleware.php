<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $originsList = $this->config->get('allow_origins');
        $origin = $request->getHeader('origin');
        $origin = $origin ? $origin[0] : false;
        if ($origin != false) {
            $isPort = (int) stripos($origin, ':', 5);
            if ($isPort) {
                $isOrigin = in_array(substr($origin, 0, $isPort), $originsList);
            } else {
                $isOrigin = in_array($origin, $originsList);
            }
            if ($isOrigin) {
                $response = Context::get(ResponseInterface::class);
                $response = $response->withHeader('Access-Control-Allow-Origin', "{$origin}")
                    ->withHeader('Access-Control-Request-Method', 'GET,POST,DELETE,PUT,OPTIONS')
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Token,DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

                Context::set(ResponseInterface::class, $response);

                if ($request->getMethod() === 'OPTIONS') {
                    return $response;
                }
            }
        }
        return $handler->handle($request);
    }
}