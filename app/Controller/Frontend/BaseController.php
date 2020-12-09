<?php
declare(strict_types=1);

/**
 * 基类
 */
namespace App\Controller\Frontend;

use App\Controller\AbstractController;
use App\Logic\Frontend\BaseLogic;
use App\Service\BaseService;
use App\Utils\Common;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\View\RenderInterface;

/**
 * @Controller()
 * Class BaseController
 * @package App\Controller\Frontend
 */
class BaseController extends AbstractController
{
    /**
     * @Inject()
     * @var BaseService
     */
    public $service;

    /**
     * @GetMapping(path="index.html[/[{option:.+}]]")
     * @param RenderInterface $render
     * @param RequestInterface $request
     * @return array
     */
    public function index(RenderInterface $render, RequestInterface $request)
    {
        $router = $request->getRequestUri();
        $page = intval($request->input('page', 1));
        $limit = intval($request->input('limit', 10));
        $page < 1 && $page = 1;
        $limit > 100 && $limit = 100;
        $searchForm = $request->has('search') ? $request->input('search') : [];
        $data = $this->service->index($page, $limit, $searchForm);
        $params = $request->all();
        return Common::display($render, $this, __FUNCTION__, compact('data', 'params', 'router'));
    }

    /**
     * @GetMapping(path="detail-{id:\d+}.html[/[{option:.+}]]")
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RenderInterface $render, RequestInterface $request)
    {
        $params = $this->validateRouteParam($request, 'id', [
            'id' => 'required|integer',
        ]);
        $router = $request->getRequestUri();
        $data = $this->service->setCondition(['id' => $params['id']])->show();
        return Common::display($render, $this, __FUNCTION__, compact('data', 'params', 'router'));
    }
}