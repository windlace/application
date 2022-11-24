<?php

namespace Package\Application;

use Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application
{
    protected array $commands;
    protected ContainerBuilder $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @throws Exception
     */
    public function run($argv)
    {
        $args = $this->readArgs($argv);

        $commandName = array_shift($args);
        $commandArgs = $args;

        unset($args);

        $this->runCommand($commandName, $commandArgs);
    }

    /**
     * @throws Exception
     */
    protected function readArgs($argv)
    {
        if (!isset($argv[1])) {
            throw new Exception("No arguments provided.");
        }

        unset($argv[0]);

        return $argv;
    }

    public function setCommands(array $commands): static
    {
        $this->commands = $commands;

        return $this;
    }

    /**
     * @throws Exception
     */
    protected function runCommand($commandName, $commandArgs)
    {
        if (!array_key_exists($commandName, $this->commands)) {
            throw new Exception("Unknown command \"{$commandName}\".");
        }

        $commandArgs = call_user_func(
            function ($commandArgs) {
                $result = [];
                while ($option = array_shift($commandArgs)) {
                    $tmp = [];
                    parse_str($option, $tmp);
                    $result[] = $tmp;
                }

                return call_user_func_array('array_merge', $result);
            },
            $commandArgs
        );

        $command = $this->container->get($this->commands[$commandName]);

        call_user_func_array([$command, 'handle'], $commandArgs);
    }
}
