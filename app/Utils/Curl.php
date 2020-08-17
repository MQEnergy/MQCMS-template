<?php

declare(strict_types=1);

namespace App\Utils;

use GuzzleHttp\Client;
use Hyperf\Guzzle\HandlerStackFactory;

class Curl
{
    /**
     * @var string
     */
    public $baseUrl = '';

    /**
     * @var array
     */
    public $options = [
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ],
    ];

    /**
     * @var int
     */
    public $timeout = 3000;

    public static $instance;

    /**
     * @return
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @var Client
     */
    public $client;

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 初始化client
     * @return Client
     */
    public function generateClient(): Client
    {
        $factory = new HandlerStackFactory();
        $stack = $factory->create();

        $this->client = make(Client::class, [
            'config' => [
                'verify' => false,
                'base_uri' => $this->baseUrl,
                'timeout' => $this->timeout,
                'handler' => $stack
            ]
        ]);
        return $this->client;
    }

    /**
     * error response
     * @param $e
     * @param string $type
     * @return array|false|string
     */
    public function errorResponse($e, $type = "json")
    {
        $data = [
            'code' => $e->getCode(),
            'msg' => $e->getMessage(),
            'data' => [],
        ];
        return $type === 'json' ? json_encode($data) : $data;
    }
}