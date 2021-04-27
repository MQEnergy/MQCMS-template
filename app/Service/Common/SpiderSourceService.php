<?php

declare(strict_types=1);

namespace App\Service\Common;

use App\Model\Common\SpiderSource;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;

class SpiderSourceService extends BaseService
{
    /**
     * @Inject()
     * @var SpiderSource
     */
    public $model;
}
