<?php

namespace Parsley;

use Illuminate\Container\Container;
use Parsley\Core\Payload;
use Parsley\Core\Task;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\SegmentTrait;


class Application implements SegmentInterface
{
    use SegmentTrait;

    /**
     * List of registered plugins
     *
     * @var \Parsley\Plugins\SegmentInterface[]
     */
    protected $plugins = [];

    // TODO: move backend to tasks?
    public function __construct(Container $container) //, TasksPool $tasks_pool, BrokerInterface $broker, Caller $caller)
    {
        $this->container = $container;

        $this->type = 'application';
        $this->name = gethostname();
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $plugin
     */
    public function registerPlugin($plugin)
    {
        if ($this->isPluginRegistered($plugin->getName())) {
            return;
        }

        // TODO: add plugins dependencies (check, conflicts, register and, maybe, deregister or replace)

        $this->fireSegmentEvent('plugin.register: ' . $plugin->getName(), [$plugin]);

        $plugin->subscribe($this->container['events']);
        $this->plugins[$plugin->getName()] = $plugin;

        $this->fireSegmentEvent('plugin.registered: ' . $plugin->getName(), [$plugin]);
    }

    public function isPluginRegistered($plugin_name)
    {
        return isset($this->plugins[$plugin_name]);
    }

    public function getRegisteredPlugins()
    {
        return $this->plugins;
    }

    public function start($priority = null)
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->container['config'];

        $priority = ($priority && in_array($priority, $config->get('parsley.priorities', [])))
            ? $priority
            : 'default';

        $config->set('parsley.priority', $priority);

        $this->fireSegmentEvent('start', [$priority]);

        return $this;
    }

    public function stop()
    {
        $this->fireSegmentEvent('stop');

        return $this;
    }

    public function send(Payload $payload)
    {
        $this->fireSegmentEvent('payload.send', [$payload]);

        // if task registered - prepare broker
        // prepare broker?
        // TODO: local call support

        return $this;
    }

    public function resend(Payload $payload)
    {
        $this->fireSegmentEvent('payload.resend', [$payload]);

        return $this;
    }

    public function drop(Payload $payload)
    {
        $this->fireSegmentEvent('payload.drop', [$payload]);

        return $this;
    }

    public function receive(Payload $payload)
    {
        $this->fireSegmentEvent('payload.receive', [$payload]);

        return $this;
    }

    public function schedule(Task $task)
    {
        /** @var \Parsley\Helpers\PayloadBuilder $payload_builder */
        $payload_builder = $this->container['parsley.payload_builder'];

        $payload = $payload_builder->fromTask($task);

        $this->send($payload);

        return $payload;
    }

//    public function local(TaskPayload $task)
//    {
//    }
}
