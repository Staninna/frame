<?php

namespace Frame\Cli\Command;

class CommandLoader
{
    /** @var Command[] */
    protected array $commands = [];

    private const PATHS = [
        'commands' => __DIR__ . '/../../commands', // TODO: Make this configurable in config
        'builtins' => __DIR__ . '/BuiltIns',
    ];

    private const NAMESPACES = [
        'commands' => 'commands', // TODO: Make this configurable in config
        'builtins' => 'Frame\Cli\Command\BuiltIns',
    ];

    public function __construct()
    {
        $this->loadCommands();
    }

    protected function loadCommands(): void
    {
        foreach (self::PATHS as $key => $path) {
            $namespace = self::NAMESPACES[$key];
            $commandFiles = glob($path . '/*.php');

            foreach ($commandFiles as $file) {
                require_once $file;
                $className = pathinfo($file, PATHINFO_FILENAME);
                $fullClassName = $namespace . '\\' . $className;
                $commandClass = new $fullClassName();
                $this->registerCommand($commandClass);
            }
        }
    }

    public function registerCommand(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function runCommand($name, $arguments): void
    {
        if (isset($this->commands[$name])) {
            $command = $this->commands[$name];
            $command->run($arguments);
        } else {
            echo "Command not found: $name\n";

            $this->help();
        }
    }

    public function help(): void
    {
        echo "Available commands:\n";
        foreach ($this->commands as $command) {
            echo "  " . $command->getName() . "\t" . $command->getDescription() . "\n";
        }
    }
}