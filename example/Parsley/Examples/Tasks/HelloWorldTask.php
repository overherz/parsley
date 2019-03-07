<?php


namespace Parsley\Examples\Tasks;

use AMQPy\Client\Properties;
use Parsley\Core\Payload;
use Parsley\Core\Task;

class HelloWorldTask extends Task
{
    protected $arguments_order = ['hello', 'world'];

    public function callback($hello, $world) // <<< TODO: should be the only valid source for arguments and their default values
    {
        echo 'Callback ' . $this->getName(), " called with hello: '{$hello}' and world: '{$world}'", PHP_EOL;
        /** @var \Parsley\Core\Payload $payload */
        $payload = $this->container['parsley.payload'];

        print_r($payload->getAttributes());
        echo PHP_EOL;
        echo $payload->getProperties()->getPriority();
        echo PHP_EOL;
        print_r(array_filter($payload->getProperties()->toArray()));
        echo PHP_EOL;
    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param mixed                             $result
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     * @param mixed                             $group_id
//     *
//     * @return mixed
//     */
//    public function onChildAfter($segment, $payload, $result, $args, $task, $group_id)
//    {
//        echo " ~Parent {$this->getName()} (#{$group_id}) notified with child's 'after' {$payload->getName()}", PHP_EOL;
//    }
//
//    public function after()
//    {
//        $tasks = [
//            'Parsley.Examples.Tasks.HiUniverse',
//        ];
//
//        $no = 0;
//
//
//        $arguments = [
//            ['hi' => date("Y-m-d H:i:s"), 'universe' => 'from hello world ' . $this->getName()],
//        ];
//
//        $this->group(
//             function () use ($tasks, $arguments) {
//
//                 /** @var \Parsley\Application $application */
//                 $application = $this->container['parsley.application'];
//
//                 foreach ($tasks as $id => $name) {
//                     $payload = new Payload($name, $arguments[$id], new Properties());
//                     $application->send($payload);
//
//                     $args = empty($arguments[$id])
//                         ? 'no arguments '
//                         : implode(', ', $arguments[$id]);
//
//                     echo "Schedule (from context of {$this->getName()}) {$name} with {$args}", PHP_EOL;
//
//                 }
//             },
//                 ['2 some' => '2 values', '2 available' => '2 across', 'hello'=> '2 group']
//        );
//
//    }
}