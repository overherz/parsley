<?php


namespace Parsley\Examples\Tasks;


use AMQPy\Client\Properties;
use Parsley\Core\Payload;
use Parsley\Core\Task;

class CallOtherTask extends Task
{
    protected $arguments_order = ['task', 'arguments'];

    public function callback($task, array $arguments) // <<< TODO: should be the only valid source for arguments and their default values
    {
        $payload = new Payload($task, $arguments, new Properties());

        /** @var \Parsley\Application $application */
        $application = $this->container['parsley.application'];

        $application->send($payload);

        $arguments = empty($arguments)
            ? 'no arguments '
            : implode(', ', $arguments);

        echo "Call {$task} with {$arguments}", PHP_EOL;
    }

    public function schedule($task, array $arguments)
    {
        $this->setArguments(func_get_args());

        /** @var \Parsley\Application $application */
        $application = $this->container['parsley.application'];

        $application->schedule($this);

        $arguments = empty($arguments)
            ? 'no arguments '
            : implode(', ', $arguments);

        echo "Schedule to call {$task} with {$arguments}", PHP_EOL;
    }
}