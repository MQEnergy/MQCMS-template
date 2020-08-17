<?php
declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Devtool\Generator\GeneratorCommand;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class ServiceCommand extends GeneratorCommand
{
    protected $name = 'mq:service';

    public function __construct()
    {
        parent::__construct($this->name);
        $this->setDescription('Create a new service class');
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
            $this->output->writeln(sprintf('<fg=red>%s</>', 'module ' . trim($this->input->getArgument('type')) . ' not exists!'));
            return 0;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        file_put_contents($path, $this->buildModelClass($name, $inputs['module'], $inputs['model']));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));

        return 0;
    }

    /**
     * @param $name
     * @param $model
     * @return string|string[]
     */
    protected function buildModelClass($name, $module, $model)
    {
        $stub = file_get_contents($this->getStub());
        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
        $stub = $this->replaceModule($stub, $module);
        return $this->replaceModel($stub, $model);
    }

    /**
     * @param $stub
     * @param $name
     * @return string|string[]
     */
    protected function replaceModel($stub, $name)
    {
        return str_replace('%MODEL%', $name, $stub);
    }

    /**
     * @param $stub
     * @param $name
     * @return string|string[]
     */
    protected function replaceModule($stub, $name)
    {
        return str_replace('%MODULE%', ucfirst(strtolower($name)), $stub);
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        $module = $this->input->getArgument('module');
        switch ($module) {
            case 'common':
                return $this->getConfig()['stub'];
                break;
            case 'frontend':
            case 'backend':
                return __DIR__ . '/stubs/other_service.stub';
                break;
            default:
                return $this->getConfig()['stub'];
                break;

        }
    }

    /**
     * @return string
     */
    protected function getDefaultNamespace(): string
    {
        $module = $this->input->getArgument('module');
        switch ($module) {
            case 'frontend':
            case 'backend':
                return 'App\\Service\\' . ucfirst(strtolower($module));
                break;
            default:
                return $this->getConfig()['namespace'];
                break;
        }
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
            'name' => trim($this->input->getArgument('name')),
            'model' => trim($this->input->getArgument('model')),
            'module' => $this->input->getArgument('module') ? trim($this->input->getArgument('module')) : 'common'
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
            ['name', InputArgument::REQUIRED, 'The name of the service class'],
            ['model', InputArgument::REQUIRED, 'The name of the model class'],
            ['module', InputArgument::OPTIONAL, 'the module type of model, eg. entry or common ...'],
        ];
    }
}
