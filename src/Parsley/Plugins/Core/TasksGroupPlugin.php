<?php


namespace Parsley\Plugins\Core;


use Illuminate\Container\Container;
use Parsley\Core\Payload;
use Parsley\Helpers\Caller;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\SegmentTrait;


class TasksGroupPlugin implements SegmentInterface
{
    use SegmentTrait;

    protected $groups = [];

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
            ['parsley.plugin: *, send', $me . 'onSend'],

            ['parsley.task: *, group.begin', $me . 'onTaskGroupBegin'],
            ['parsley.task: *, group.end', $me . 'onTaskGroupEnd'],

            ['parsley.*: *, before.payload.handling: *', $me . 'onBeforePayloadHandling'],
            ['parsley.*: *, failed.payload.handling: *', $me . 'onFailedPayloadHandling'],
            ['parsley.*: *, successful.payload.handling: *', $me . 'onSuccessfulPayloadHandling'],
            ['parsley.*: *, done.payload.handling: *', $me . 'onDonePayloadHandling'],
        ];
    }

    /**
     * @param SegmentInterface      $segment
     * @param \Parsley\Core\Payload $payload
     *
     * @return mixed
     */
    public function onSend($segment, $payload)
    {
        if (!$this->inGroup()) {
            return null;
        }

        list($group, $group_id, $attributes) = last($this->groups);


        // update headers and attrs
        $headers = $payload->getHeaders();

        $headers['parsley_group']    = $group;
        $headers['parsley_group_id'] = $group_id;

        $attributes = array_merge($payload->getAttributes(), $attributes);

        $payload->setAttributes($attributes);
        $payload->setHeaders($headers);
    }

    /**
     * @param SegmentInterface $segment
     * @param array            $attributes
     */
    public function onTaskGroupBegin($segment, $attributes)
    {
        $payload_alias = 'parsley.payload';

        if (!$this->container->isAlias($payload_alias) && !$this->container->bound($payload_alias)) {
            // if we have no payload it means that no group can be formed and grouped tasks should be scheduled as standalone
            return;
        }

        /** @var Payload $payload */
        $payload = $this->container->make($payload_alias);

        // if we have group request from other than current grouped one, then switch context to it
        if ($this->hasGroup($payload) && $this->getGroupName($payload) == $segment->getName()) {
            $group_id   = $this->getGroupId($payload);
            $group      = $this->getGroupName($payload);
            $attributes = is_null($attributes) ? $payload->getAttributes() : $attributes;
        } else {
            $group_id   = $payload->getId();
            $group      = $payload->getName();
            $attributes = (array)$attributes;
        }

        $this->groups[] = [$group, $group_id, $attributes];
    }

    /**
     * @param SegmentInterface      $segment
     * @param \Parsley\Core\Payload $payload
     */
    public function onTaskGroupEnd($segment, $payload)
    {
        array_pop($this->groups);
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
        return $this->notifyParent($payload, 'onChildBefore', [$segment, $payload, $args, $task]);
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
        return $this->notifyParent($payload, 'onChildFailure', [$segment, $payload, $exception, $args, $task]);
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
        return $this->notifyParent($payload, 'onChildAfter', [$segment, $payload, $result, $args, $task]);
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
        return $this->notifyParent($payload, 'onChildAlways', [$segment, $payload, $exception, $result, $args, $task], $exception);
    }

    /**
     * @param \Parsley\Core\Payload $payload
     * @param string                $method
     * @param array                 $arguments
     * @param \Exception            $exception
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function notifyParent($payload, $method, array $arguments, $exception = null)
    {
        if (!$this->hasGroup($payload)) {
            return null;
        }

        $group    = $this->getGroupTask($payload);
        $group_id = $this->getGroupId($payload);

        $arguments[] = $group_id;

        if (is_callable([$group, $method])) {
            return Caller::call($group, $method, $arguments);
        } elseif ($exception) {
            throw $exception;
        }

        return null;
    }

    public function inGroup()
    {
        return last($this->groups);
    }

    /**
     * @param \Parsley\Core\Payload $payload
     *
     * @return bool
     */
    protected function hasGroup($payload)
    {
        $headers = $payload->getHeaders();

        return isset($headers['parsley_group']) && isset($headers['parsley_group_id']);
    }

    /**
     * @param \Parsley\Core\Payload $payload
     *
     * @return bool
     */
    protected function getGroupName($payload)
    {
        return $payload->getHeaders()['parsley_group'];
    }

    /**
     * @param \Parsley\Core\Payload $payload
     *
     * @return bool
     */
    protected function getGroupTask($payload)
    {
        /** @var \Parsley\Core\TasksPool $tasks_pool */
        $tasks_pool = $this->container['parsley.tasks_pool'];

        return $tasks_pool->get($payload->getHeaders()['parsley_group']);
    }

    /**
     * @param \Parsley\Core\Payload $payload
     *
     * @return bool
     */
    protected function getGroupId($payload)
    {
        return $payload->getHeaders()['parsley_group_id'];
    }
} 