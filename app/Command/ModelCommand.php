<?php
declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Schema\MySqlBuilder;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class ModelCommand extends GeneratorCommand
{
    protected $name = 'mq:model';

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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->resolver = $this->container->get(ConnectionResolverInterface::class);
        $this->config = $this->container->get(ConfigInterface::class);
        parent::__construct($this->name);
        $this->setDescription('Create a new model class');
    }

    /**
     * Execute the console command.
     *
     * @return null|bool
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $inputs = $this->getNameInput();
        if ($inputs['name']) {
            $name = $this->qualifyClass($inputs['name']);
            $path = $this->getPath($name);

            // First we will check to see if the class already exists. If it does, we don't want
            // to create the class and overwrite the user's code. So, we will bail out so the
            // code is untouched. Otherwise, we will continue generating this class' files.
            if (($input->getOption('force') === false) && $this->alreadyExists($inputs['name'])) {
                $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
                return 0;
            }

            if (!$this->getStub()) {
                $this->output->writeln(sprintf('<fg=red>%s</>', 'model stub not exists!'));
                return 0;
            }

            // Next, we will generate the path to the location where this class' file should get
            // written. Then, we will build the class and make the proper replacements on the
            // stub files so that it gets the correctly formatted namespace and class name.
            $this->makeDirectory($path);

            file_put_contents($path, $this->buildModelClass($name));

            $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));
        } else {
            $tables = $this->getTables();

            foreach ($tables as $table) {
                $name = $this->qualifyClass($table);
                $path = $this->getPath($name);

                if (!$this->getStub()) {
                    $this->output->writeln(sprintf('<fg=red>%s</>', 'model stub not exists!'));
                }
                // First we will check to see if the class already exists. If it does, we don't want
                // to create the class and overwrite the user's code. So, we will bail out so the
                // code is untouched. Otherwise, we will continue generating this class' files.
                if (($input->getOption('force') === false) && $this->alreadyExists($table)) {
                    $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
                } else {
                    $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));
                }

                // Next, we will generate the path to the location where this class' file should get
                // written. Then, we will build the class and make the proper replacements on the
                // stub files so that it gets the correctly formatted namespace and class name.
                $this->makeDirectory($path);

                file_put_contents($path, $this->buildModelClass($name));

            }
        }

        return 0;
    }

    /**
     * @param $name
     * @return string|string[]
     */
    protected function buildModelClass($name)
    {
        $stub = file_get_contents($this->getStub());
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return $this->getConfig()['stub'];
    }

    /**
     * @return string
     */
    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'];
    }

    /**
     * Get the custom config for generator.
     */
    protected function getConfig(): array
    {
        $class = Arr::last(explode('\\', static::class));
        $class = Str::replaceLast('Command', '', $class);
        $key = 'devtool.mqcms.' . Str::snake($class, '.');
        return $this->getContainer()->get(ConfigInterface::class)->get($key) ?? [];
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return [
            'name' => $this->input->getArgument('name') ? trim($this->input->getArgument('name')) : '',
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
            ['name', InputArgument::OPTIONAL, 'The name of the model class'],
        ];
    }


    /**
     * 获取所有表
     * @return array|bool
     */
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
