<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\BaseService;
use App\Service\Common\SpiderSourceService;
use App\Utils\Common;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class DataCleanCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject()
     * @var SpiderSourceService
     */
    public $sourceService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('data:clean');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('data clean');
    }

    public function handle()
    {
        $type = $this->getNameInput()['type'];
        $date = $this->getNameInput()['date'];
        $service = 'App\Service\Common\MtSource' . $date . 'Service';
        $reflectionClass = new \ReflectionClass($service);
        $mtSource = $reflectionClass->newInstance();
        if (!($mtSource instanceof BaseService)) {
            $this->error('此月数据不存在');
            return false;
        }
        $sourceList = $this->sourceService->setCondition(['type' => $type])->get();
        $_source = [];
        foreach ($sourceList as $key => $value) {
            $dataList = json_decode($value['data'], true);
            foreach ($dataList as $k => $val) {
                $_source[] = [
                    'uuid' => Common::generateSnowId(),
                    'source_uuid' => $value['uuid'],
                    'keyword' => $value['keyword'],
                    'goods_name' => $val['name'],
                    'goods_price' => $val['price'],
                    'sale_num' => $val['sale_num'],
                    'created_at' => time(),
                ];
            }
        }
        $_source = array_unique($_source, SORT_REGULAR);
        $this->mtSource->multiTableJoinQueryBuilder()->insert($_source);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return [
            'type' => trim($this->input->getArgument('type')),
            'date' => $this->input->getArgument('date') ? trim($this->input->getArgument('date')) : date('Ym'),
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['type', InputArgument::REQUIRED, 'mt ele jd jdjk'],
            ['date', InputArgument::OPTIONAL, '年月 如：202104'],
        ];
    }
}
