<?php


namespace Parsley\Plugins\Core;


use Illuminate\Container\Container;
use Parsley\Helpers\Caller;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\PluginTrait;
use Parsley\Plugins\SegmentTrait;


class TaskNotifierPlugin implements SegmentInterface
{
    use SegmentTrait;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->type = 'plugin';
        $this->name = get_class($this);
    }

    public function handles()
    {
        $me = __CLASS__ . '@';

        return [
            ['parsley.*: *, before.payload.handling: *', $me . 'onBeforePayloadHandling'],
            ['parsley.*: *, failed.payload.handling: *', $me . 'onFailedPayloadHandling'],
            ['parsley.*: *, successful.payload.handling: *', $me . 'onSuccessfulPayloadHandling'],
            ['parsley.*: *, done.payload.handling: *', $me . 'onDonePayloadHandling'],
        ];
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     *
     * @return mixed
     */
    public function onBeforePayloadHandling($segment, $payload, $args, $task)
    {
        return $this->notifyTask($task, 'before', [$segment, $payload, $args, $task]);
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param \Exception                        $exception
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     *
     * @return mixed
     */
    public function onFailedPayloadHandling($segment, $payload, $exception, $args, $task)
    {
        return $this->notifyTask($task, 'failure', [$segment, $payload, $exception, $args, $task]);
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param mixed                             $result
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     *
     * @return mixed
     */
    public function onSuccessfulPayloadHandling($segment, $payload, $result, $args, $task)
    {
        return $this->notifyTask($task, 'after', [$segment, $payload, $result, $args, $task]);
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param \Exception                        $exception
     * @param mixed                             $result
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     *
     * @throws \Exception
     * @return mixed
     */
    public function onDonePayloadHandling($segment, $payload, $exception, $result, $args, $task)
    {
        return $this->notifyTask($task, 'always', [$segment, $payload, $exception, $result, $args, $task], $exception);
    }

    /**
     * @param \Parsley\Core\Task $task
     * @param string             $method
     * @param array              $arguments
     * @param \Exception         $exception
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function notifyTask($task, $method, array $arguments, $exception = null)
    {
        if (is_callable([$task, $method])) {
            return Caller::call($task, $method, $arguments);
        } elseif ($exception) {
            throw $exception;
        }

        return null;
    }
}
