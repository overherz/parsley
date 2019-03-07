<?php


namespace Parsley\Core;


// TODO: arguments list
// TODO: closures

// TODO: generate helpers for method arguments

use Illuminate\Container\Container;
use Parsley\Helpers\Namer;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\SegmentTrait;


class Task implements SegmentInterface
{
    use SegmentTrait;

    protected $arguments;
    protected $arguments_order = []; // TODO: override, NOTE: arguments should be consistent

    /**
     * @var array
     */
    private $default_properties = [];

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->type = 'task';
        $this->name = Namer::getCanonicalName($this, 'Task');
    }

    public function getArgumentsOrder()
    {
        return $this->arguments_order;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getDefaultProperties()
    {
        return $this->default_properties;
    }

    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

//    public function schedule( /* arguments list*/)
//    {
//        $this->setArguments(func_get_args()); // or [/* arguments list*/]
//
//        /** @var \Parsley\Application $application */
//        $application = $this->container['parsley.application'];
//
//        $application->schedule($this);
//    }

    public function group(callable $callback, array $attributes = null)
    {
        try {
            $this->fireSegmentEvent('group.begin', [$attributes]);
            call_user_func($callback);
        } finally {
            $this->fireSegmentEvent('group.end', [$attributes]);
        }
    }

    // TODO: this part should be auto-generated
//    public function run(/* arguments list*/)
//    {
//        $this->arguments = $this->packArguments(func_get_args());
//
//        $result = $this->publisher->run($this);
//
//        return $result;
//    }

//    public function local( /* arguments list*/)
//    {
//        $this->arguments = $this->packArguments(func_get_args());
//
//        $result = $this->publisher->local($this);
//
//        return $result;
//    }


//    /**
//     * @return mixed
//     */
//    public function callback( /* arguments list*/) // <<< TODO: should be the only valid source for arguments and their default values
//    {
//        // returns plain result that SHOULD be serializable
//        // what about exceptions? who cares? i propose to delegate this to responder
//        // Result object
//    }

//    // NOTE: \Parsley\Plugins\Core\TaskNotifierPlugin will handle this for you
//    public function handles()
//    {
//        $me   = __CLASS__ . '@';
//        $name = $this->getName();
//
//        return [
//            ["parsley.*: *, before.payload.handling: {$name}", $me . 'onBeforePayloadHandling'],
//            ["parsley.*: *, failed.payload.handling: {$name}", $me . 'onFailedPayloadHandling'],
//            ["parsley.*: *, successful.payload.handling: {$name}", $me . 'onSuccessfulPayloadHandling'],
//            ["parsley.*: *, done.payload.handling: {$name}", $me . 'onDonePayloadHandling'],
//        ];
//    }
//
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @return mixed
//     */
//    public function before($segment, $payload, $args, $task)
//    {
//    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param \Exception                        $exception
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @return mixed
//     */
//    public function failure($segment, $payload, $exception, $args, $task)
//    {
//    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param mixed                             $result
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @return mixed
//     */
//    public function after($segment, $payload, $result, $args, $task)
//    {
//    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param \Exception                        $exception
//     * @param mixed                             $result
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @throws \Exception
//     * @return mixed
//     */
//    public function always($segment, $payload, $exception, $result, $args, $task)
//    {
//        if ($exception) {
//            throw $exception;
//        }
//    }
}
