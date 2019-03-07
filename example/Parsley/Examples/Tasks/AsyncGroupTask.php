<?php


namespace Parsley\Examples\Tasks;


use AMQPy\Client\Properties;
use Parsley\Core\Payload;
use Parsley\Core\Task;

class AsyncGroupTask extends Task
{
    protected $arguments_order = ['tasks', 'arguments'];

    public function callback(array $tasks, array $arguments) // <<< TODO: should be the only valid source for arguments and their default values
    {
        if (count($tasks) != count($arguments)) {
            throw new \Exception('Task and arguments count mismatch');
        }

        $this->group(
             function () use ($tasks, $arguments) {

                 /** @var \Parsley\Application $application */
                 $application = $this->container['parsley.application'];

                 foreach ($tasks as $id => $name) {
                     $payload = new Payload($name, $arguments[$id], new Properties());
                     $application->send($payload);

                     $args = empty($arguments[$id])
                         ? 'no arguments '
                         : implode(', ', $arguments[$id]);

                     echo "Schedule {$name} with {$args}", PHP_EOL;

                 }
             },
                 ['some' => 'values', 'available' => 'across', 'the' => 'async group']
        );
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     * @param mixed                             $group_id
     *
     * @return mixed
     */
    public function onChildBefore($segment, $payload, $args, $task, $group_id)
    {
        #echo " ~Parent {$this->getName()} (#{$group_id}) notified with child's 'before' {$payload->getName()}", PHP_EOL;

//        print_r($payload->getAttributes());
//        echo PHP_EOL;
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param \Exception                        $exception
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     * @param mixed                             $group_id
     *
     * @return mixed
     */
    public function onChildFailure($segment, $payload, $exception, $args, $task, $group_id)
    {
        #echo " ~Parent {$this->getName()} (#{$group_id}) notified with child's 'failure' {$payload->getName()}", PHP_EOL;

//        print_r($payload->getAttributes());
//        echo PHP_EOL;
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param mixed                             $result
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     * @param mixed                             $group_id
     *
     * @return mixed
     */
    public function onChildAfter($segment, $payload, $result, $args, $task, $group_id)
    {
        echo " ~Parent {$this->getName()} (#{$group_id}) notified with child's 'after' {$payload->getName()}", PHP_EOL;


        if ($payload->getName() != 'Parsley.Examples.Tasks.HelloWorld') {
            return;
        }

        $tasks = [
            'Parsley.Examples.Tasks.HiUniverse',
        ];

        $no = 0;


        $arguments = [
            ['hi' => date("Y-m-d H:i:s"), 'universe' => 'from_async ' . $this->getName()],
        ];

        $this->group(
             function () use ($tasks, $arguments) {

                 /** @var \Parsley\Application $application */
                 $application = $this->container['parsley.application'];

                 foreach ($tasks as $id => $name) {
                     $payload = new Payload($name, $arguments[$id], new Properties());
                     $application->send($payload);

                     $args = empty($arguments[$id])
                         ? 'no arguments '
                         : implode(', ', $arguments[$id]);

                     echo "Schedule (from context of {$this->getName()}) {$name} with {$args}", PHP_EOL;

                 }
             }
        );


        echo PHP_EOL;
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     * @param \Exception                        $exception
     * @param mixed                             $result
     * @param array                             $args
     * @param \Parsley\Core\Task                $task
     * @param mixed                             $group_id
     *
     * @throws \Exception
     * @return mixed
     */
    public function onChildAlways($segment, $payload, $exception, $result, $args, $task, $group_id)
    {
        #echo " ~Parent {$this->getName()} (#{$group_id}) notified with child's 'always' {$payload->getName()}", PHP_EOL;

//        print_r($payload->getAttributes());
//        echo PHP_EOL;

        if ($exception) {
            throw $exception;
        }
    }

    public function schedule(array $tasks, array $arguments)
    {
        $this->setArguments([$tasks, $arguments]);

        /** @var \Parsley\Application $application */
        $application = $this->container['parsley.application'];

        $application->schedule($this);

        $tasks = implode(', ', $tasks);

        echo "Schedule to call {$tasks}", PHP_EOL;
    }
}