<?php
declare(strict_types=1);

namespace App\Command;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Utils\Common;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

/**
 * @Command()
 * Class InitializeCommand
 * @package App\Command
 */
class InitializeCommand extends HyperfCommand
{
    /**
     * @var string
     */
    protected $name = 'mq:init';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConnectionResolverInterface
     */
    protected $resolver;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @Inject()
     * @var RequestInterface
     */
    protected $request;

    /**
     * InitCommand constructor.
     * @param string|null $name
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->resolver = $this->container->get(ConnectionResolverInterface::class);
        $this->config = $this->container->get(ConfigInterface::class);
        parent::__construct($this->name);
    }

    /**
     * configure
     */
    protected function configure()
    {
        parent::configure();
        $this->setDescription('initialization application');
    }

    /**
     * Handle the console command.
     */
    public function handle()
    {
        if (file_exists(BASE_PATH . '/init.lock')) {
            $choice = $this->choice('你已初始化项目，确定需要重新初始化吗?', ['No', 'Yes']);
            if ($choice !== 'Yes') {
                return false;
            }
        }
        $choice = $this->choice('是否执行migrate?', ['No', 'Yes']);
        if ($choice === 'Yes') {
            $this->initMigration();
        }
        $choice = $this->choice('是否初始化所有Model类？', ['No', 'Yes']);
        if ($choice === 'Yes') {
            $this->initModel();

            $choice = $this->choice('是否基于Model初始化所有Service类？', ['No', 'Yes']);
            if ($choice === 'Yes') {
                $this->initService();

                $choice = $this->choice('是否初始化所有Controller类？', ['No', 'Yes']);
                if ($choice === 'Yes') {
                    $this->initController();
                }
            }
        }
        $choice = $this->choice('是否初始化一个后台账号密码？', ['No', 'Yes']);
        if ($choice === 'Yes') {
            $this->initAccount();
        }
        $this->info('Initialization successfully.');
    }

    /**
     * init migration
     */
    public function initMigration()
    {
        return $this->call('migrate');
    }

    /**
     * init model
     */
    public function initModel()
    {
        return $this->call('gen:model', [
            '--pool' => 'default',
            '--path' => 'app/Model/Common',
            '--with-comments' => true,
            '--prefix' => env('DB_PREFIX')
        ]);
    }

    /**
     * init service
     */
    public function initService()
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $this->call('mq:service', [
                '-N' => 'App\\Service\\Common',
                'name' => $table . 'Service',
                'model' => $table,
                'module' => 'common'
            ]);
        }
    }

    /**
     * init controller
     */
    public function initController()
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $this->call('mq:controller', [
                '-N' => 'App\\Controller\\Backend',
                'name' => $table . 'Controller',
                'service' => $table . 'Service',
                'module' => 'common'
            ]);
        }
    }

    /**
     * init account
     * @return bool
     */
    public function initAccount()
    {
        file_put_contents(BASE_PATH . '/init.lock', 1);

        $account = $this->ask('账号');
        $password = $this->ask('密码');

        try {
            $adminInfo = Db::table('admin')->select('id')->where('account', $account)->first();
            if ($adminInfo) {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '此账号名已存在');
            }
            $salt = Common::generateSalt();
            $uuid = Common::generateSnowId();
            $data = [
                'uuid' => $uuid,
                'account' => $account,
                'password' => Common::generatePasswordHash($password, $salt),
                'phone' => '',
                'avatar' => '',
                'status' => 1,
                'salt' => $salt,
                'register_time' => time(),
                'register_ip' => '127.0.0.1',
                'login_time' => time(),
                'login_ip' => '127.0.0.1'
            ];
            $res = Db::table('admin')->insert($data);
            if (!$res) {
                throw new BusinessException(ErrorCode::BAD_REQUEST, '新建账号失败，请检查数据库连接');
            }
            $this->info('账号：' . $account . ' 密码：' . $password . ' 请记住账号密码');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return false;
        }
    }

    protected function getTables()
    {
        if (env('DB_PREFIX') == '') {
            $this->error('数据表前缀 DB_PREFIX 未设置');
            return false;
        }
        $option = new ModelOption();
        $option->setPool('default');
        $builder = $this->getSchemaBuilder($option->getPool());
        $tables = [];
        foreach ($builder->getAllTables() as $row) {
            $row = (array) $row;
            $table = reset($row);
            $table = Str::replaceFirst(env('DB_PREFIX'), '', $table);
            if (! $this->isIgnoreTable($table, $option)) {
                $tables[] = $option->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
            }
        }
        return $tables;
    }

    protected function getSchemaBuilder(string $poolName): MySqlBuilder
    {
        $connection = $this->resolver->connection($poolName);
        return $connection->getSchemaBuilder();
    }

    protected function isIgnoreTable(string $table, ModelOption $option): bool
    {
        if (in_array($table, $option->getIgnoreTables())) {
            return true;
        }
        return $table === $this->config->get('databases.migrations', 'migrations');
    }

}