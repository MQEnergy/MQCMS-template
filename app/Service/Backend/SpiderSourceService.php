<?php

declare(strict_types=1);

namespace App\Service\Backend;

use App\Model\Entity\SpiderSource;
use Hyperf\Di\Annotation\Inject;

class SpiderSourceService extends \App\Service\Common\SpiderSourceService
{
    /**
     * @Inject()
     * @var SpiderSource
     */
    public $model;
}
