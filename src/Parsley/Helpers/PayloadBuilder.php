<?php


namespace Parsley\Helpers;


use AMQPy\Client\Delivery;
use AMQPy\Client\Properties;
use Illuminate\Container\Container;
use Parsley\Core\Payload;
use Parsley\Core\Task;
use Parsley\Helpers\Exceptions\PayloadBuilderException;


class PayloadBuilder
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Payload $payload
     *
     * @return array
     */
    public function toArray(Payload $payload)
    {
        return [
            'name'       => $payload->getName(),
            'arguments'  => $payload->getArguments(),
            'attributes' => $payload->getAttributes(),
        ];
    }

    public function fromDelivery(array $payload, Delivery $delivery)
    {
        $required = ['name', 'arguments', 'attributes'];

        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                throw new PayloadBuilderException("Required payload field '{$field}' missed");
            }
        }

        return new Payload($payload['name'], $payload['arguments'], $delivery->getProperties(), $payload['attributes']);
    }

    public function fromTask(Task $task)
    {
        $arguments  = Caller::packArguments($task->getArguments(), $task->getArgumentsOrder());
        $properties = new Properties($task->getDefaultProperties());

        return new Payload($task->getName(), $arguments, $properties);
    }
}