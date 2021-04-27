<?php

declare(strict_types=1);

namespace App\Controller\Frontend;

use App\Service\Frontend\SpiderSourceService;
use App\Utils\Common;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @AutoController()
 * Class SpiderController
 * @package App\Controller\Frontend
 */
class SpiderController extends BaseController
{
    /**
     * @Inject()
     * @var SpiderSourceService
     */
    public $service;

    /**
     * @param RequestInterface $request
     * @return array
     */
    public function create(RequestInterface $request)
    {
        $post = $this->validateParam($request, [
            'data' => 'required',
            'type' => 'required',
            'imei' => 'required',
            'keyword' => 'required',
            'fingerprint' => 'required',
        ]);
        $res = $this->service->setData([
            'uuid' => Common::generateSnowId(),
            'keyword' => $post['keyword'],
            'fingerprint' => $post['fingerprint'],
            'type' => $post['type'],
            'imei' => $post['imei'],
            'data' => $post['data'],
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d'),
        ])->store();
        return ['code' => 200, 'data' => $res, 'message' => 'OK'];
    }

}
