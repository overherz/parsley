<?php


namespace Parsley\Plugins\Brokers;


use AMQPy\AbstractConsumer;
use AMQPy\AbstractListener;
use AMQPy\Client\Delivery;
use Exception;
use Illuminate\Container\Container;
use Parsley\Core\Payload;
use Parsley\Core\Task;
use Parsley\Exceptions\ParsleyException;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\SegmentTrait;


class RabbitMQ extends AbstractConsumer implements SegmentInterface, BrokerInterface
{
    use SegmentTrait;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->type = 'broker';
        $this->name = get_class($this);
    }

    public function setup()
    {
        /** @var \Parsley\Core\TasksPool $tasks_pool */
        $tasks_pool = $this->container['parsley.tasks_pool'];

        /** @var \AMQPy\Publisher $publisher */
        $publisher = $this->container['parsley.brokers.rabbitmq.publisher'];

        /** @var \AMQPy\AbstractListener $listener */
        $listener = $this->container['parsley.brokers.rabbitmq.listener'];

        $queue    = $listener->getQueue();
        $exchange = $publisher->getExchange();

        $priority   = $this->container['config']->get('parsley.priority', 'default');

        /** @var Task $task */
        foreach ($tasks_pool->getAll() as $task) {
            $routing_key = $priority . '.' . $task->getName();
            $queue->bind($exchange->getName(), $routing_key);
        }
    }

    public function start()
    {
        /** @var \AMQPy\AbstractListener $listener */
        $listener = $this->container['parsley.brokers.rabbitmq.listener'];

        $listener->consume($this);
    }

    public function stop()
    {
        parent::stop();
    }

    public function cleanup()
    {
    }

    public function send(Payload $payload)
    {
        $this->fireSegmentEvent('send', [$payload]);

        /** @var \AMQPy\Publisher $publisher */
        $publisher = $this->container['parsley.brokers.rabbitmq.publisher'];

        /** @var \Parsley\Helpers\PayloadBuilder $payload_builder */
        $payload_builder = $this->container['parsley.payload_builder'];

        $props = $payload->getProperties();

        $priority = $props->getPriority();

        if (null === $priority) {
            $priority = $this->container['config']->get('parsley.priority', 'default');
        } else {
            /** @var \Illuminate\Config\Repository $config */
            $config = $this->container['config'];

            $priorities_map = $config->get('parsley.priorities');

            $priority = array_get($priorities_map, $priority, 'default');
        }

        $routing_key = $priority . '.' . $payload->getName();

        if (!is_numeric($priority)) {
            $props->setPriority(null);
        }

        $publisher->publish($payload_builder->toArray($payload), $routing_key, $payload->getProperties());
        $this->fireSegmentEvent('sent', [$payload, $priority, $routing_key]);
    }

    public function resend(Payload $payload)
    {
        $this->checkDelivery($payload);

        /** @var \AMQPy\AbstractListener $listener */
        $listener = $this->container['parsley.brokers.rabbitmq.listener'];

        $listener->resend($payload->getDelivery());
    }

    public function drop(Payload $payload)
    {
        $this->checkDelivery($payload);

        /** @var \AMQPy\AbstractListener $listener */
        $listener = $this->container['parsley.brokers.rabbitmq.listener'];

        $listener->drop($payload->getDelivery());
    }

    public function receive(Payload $payload)
    {
        $this->fireSegmentEvent('payload.receive', [$payload]);
    }

    /**
     * Process received data from queued message.
     */
    public function consume($raw_payload, Delivery $delivery, AbstractListener $listener)
    {
        /** @var \Parsley\Helpers\PayloadBuilder $payload_builder */
        $payload_builder = $this->container['parsley.payload_builder'];

        $payload = $payload_builder->fromDelivery($raw_payload, $delivery);

        $this->container->instance('parsley.payload', $payload);
        $payload->setDelivery($delivery);

        $this->receive($payload);

        $this->container->forgetInstance('parsley.payload');
    }

    public function after($result, Delivery $delivery, AbstractListener $listener)
    {
        // TODO: event
        // /** @var \AMQPy\AbstractListener $listener */
        // $listener = $this->container['parsley.brokers.rabbitmq.listener'];

        $listener->accept($delivery);
    }

    public function failure(Exception $e, Delivery $delivery, AbstractListener $listener)
    {
        // TODO: take care of invalid args order (do not silence)!
        // TODO: event (events support in listener?)
        // /** @var \AMQPy\AbstractListener $listener */
        // $listener = $this->container['parsley.brokers.rabbitmq.listener'];

        // TODO: move to plugin (via event)
        // Try to redeliver if not redelivered, otherwise just drop it
        // if ($delivery->getEnvelope()->isRedeliver()) {
        //     $listener->drop($delivery);
        // } else {
        //     $listener->resend($delivery);
        // }

        $listener->drop($delivery);
    }

    protected function checkDelivery(Payload $payload)
    {
        if (!$payload->hasDelivery()) {
            throw new ParsleyException("Payload has no delivery");
        }
    }
}
