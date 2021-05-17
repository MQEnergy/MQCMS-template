<?php

declare(strict_types=1);

namespace App\Service\Common;

use App\Model\Common\MtSource202104;
use App\Service\BaseService;
use Hyperf\Di\Annotation\Inject;

class MtSource202104Service extends BaseService
{
    /**
     * @Inject()
     * @var MtSource202104
     */
    public $model;
}
