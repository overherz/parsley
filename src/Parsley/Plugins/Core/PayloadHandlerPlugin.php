<?php


namespace Parsley\Plugins\Core;

use Illuminate\Container\Container;
use Parsley\Core\Payload;
use Parsley\Core\TasksPool;
use Parsley\Helpers\Caller;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\PluginTrait;
use Parsley\Plugins\SegmentTrait;


class PayloadHandlerPlugin implements SegmentInterface
{
    use SegmentTrait;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->type = 'plugin';
        $this->name = get_class($this);
    }

    /**
     * Return array of events and their handlers (with optional priority).
     *
     * @return array
     */
    public function handles()
    {
        $me = __CLASS__ . '@';

        return [
            ['parsley.*: *, unknown.payload.received: *', $me . 'onUnknownPayloadReceived'],
            ['parsley.*: *, known.payload.received: *', $me . 'onKnownPayloadReceived'],
        ];
    }

    public function onUnknownPayloadReceived($plugin, Payload $payload, $is_known)
    {
        // TODO: use custom policy, for example, redeliver when not found (via config, for example)

        // standard drop mechanism
        /** @var \Parsley\Application $application */
        $application = $this->container['application'];
        $application->drop($payload);
    }

    public function onKnownPayloadReceived($plugin, Payload $payload, $is_known)
    {
        /** @var TasksPool $tasks_pool */
        $tasks_pool = $this->container['parsley.tasks_pool'];

        $task_name = $payload->getName();

        $task = $tasks_pool->get($task_name);
        $args = Caller::unpackArguments($payload->getArguments(), $task->getArgumentsOrder());

        // TODO: event + subscriber
        // $task->before($payload, $args);

        $this->fireSegmentEvent('before.payload.handling: ' . $task_name, [$payload, $args, $task]);

        $exception = null;
        $result    = null;

        try {
            // TODO: event + subscriber
            $result = Caller::call($task, 'callback', $args);
        } catch (\Exception $e) {
            $exception = $e;
        }

        if ($exception) {
            // TODO: event + subscriber
            // $task->failure($exception, $args, $payload);

            $this->fireSegmentEvent('failed.payload.handling: ' . $task_name, [$payload, $exception, $args, $task]);
        } else {
            // TODO: event + subscriber
            //$task->after($result, $args, $payload);

            $this->fireSegmentEvent('successful.payload.handling: ' . $task_name, [$payload, $result, $args, $task]);
        }

        // TODO: event + subscriber
        // $task->always($result, $args, $payload, $exception);
        $this->fireSegmentEvent('done.payload.handling: ' . $task_name, [$payload, $exception, $result, $args, $task]);
    }
}
