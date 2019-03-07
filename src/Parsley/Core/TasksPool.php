<?php


namespace Parsley\Core;


use Illuminate\Container\Container;
use Parsley\Core\Exceptions\TasksPoolException;


class TasksPool
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * @var Task[]
     */
    private $tasks = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register($task)
    {
        if (is_array($task)) {
            foreach ($task as $name) {
                $this->register($name);
            }

            return $this;
        }

        if (!is_object($task)) {
            $task = $this->container->make($task);
        }

        /** @var Task $task */

        $this->tasks[$task->getName()] = $task;
        $task->subscribe($this->container['events']);

        return $this;
    }

    public function deregister($name)
    {
        unset($this->tasks[$name]);

        // NOTE: we still have task event handlers active

        return $this;
    }

    public function isRegistered($name)
    {
        return isset($this->tasks[$name]);
    }

    public function getAll()
    {
        return $this->tasks;
    }

    public function get($name)
    {
        if (!isset($this->tasks[$name])) {
            throw new TasksPoolException("Task '{$name}' not found");
        }

        return $this->tasks[$name];
    }
}