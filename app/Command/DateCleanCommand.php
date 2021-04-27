<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Common\MtSource202104Service;
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
class DateCleanCommand extends HyperfCommand
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

    /**
     * @Inject()
     * @var MtSource202104Service
     */
    public $mtSource202104;

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
        $type = $this->input->getArgument('type');
        $sourceList = $this->sourceService->setCondition(['type' => $type])->get();
        $_source = [];
        foreach ($sourceList as $key => $value) {
            $dataList = json_decode($value['data'], true);
            foreach ($dataList as $k => $val) {
                $_source[] = [
                    'uuid' => Common::generateSnowId(),
                    'source_uuid' => $value['uuid'],
                    'keyword' => '国胜大药房（滨湖新区三店）',
                    'goods_name' => $val['name'],
                    'goods_price' => $val['price'],
                    'sale_num' => $val['sale_num'],
                    'created_at' => time(),
                ];
            }
        }
        // $_source = array_unique($_source, SORT_REGULAR);
        // print_r($_source);
        $this->mtSource202104->multiTableJoinQueryBuilder()->insert($_source);
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
            ['type', InputArgument::REQUIRED, ' mt ele jd jdjk'],
        ];
    }
}
