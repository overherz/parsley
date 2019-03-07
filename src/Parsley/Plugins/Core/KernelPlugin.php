<?php


namespace Parsley\Plugins\Core;

use Illuminate\Container\Container;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\SegmentTrait;


class KernelPlugin implements SegmentInterface
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
            ['parsley.application: *, start', $me . 'onApplicationStart'],
            ['parsley.application: *, stop', $me . 'onApplicationStop'],

            ['parsley.*: *, payload.send', $me . 'onPayloadSend'],
            ['parsley.*: *, payload.resend', $me . 'onPayloadResend'],
            ['parsley.*: *, payload.drop', $me . 'onPayloadDrop'],
            ['parsley.*: *, payload.receive', $me . 'onPayloadReceive'],
        ];
    }

    /**
     * @param SegmentInterface $segment
     */
    public function onApplicationStart($segment)
    {
        $this->fireSegmentEvent('start');

        /** @var \Parsley\Plugins\Brokers\BrokerInterface $broker */
        $broker = $this->container['parsley.broker'];

        $broker->setup();
        $broker->start();

        // NOTE: when we have blocking broker this event will be raised AFTER broker stop
        // TODO: deal with it
        $this->fireSegmentEvent('started');
    }

    /**
     * @param SegmentInterface $segment
     */
    public function onApplicationStop($segment)
    {
        $this->fireSegmentEvent('stop');

        /** @var \Parsley\Plugins\Brokers\BrokerInterface $broker */
        $broker = $this->container['parsley.broker'];

        $broker->stop();
        $broker->cleanup();

        $this->fireSegmentEvent('stopped');
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param \Parsley\Core\Payload             $payload
     */
    public function onPayloadSend($segment, $payload)
    {
        $this->fireSegmentEvent('send', [$payload]);

        /** @var \Parsley\Plugins\Brokers\BrokerInterface $broker */
        $broker = $this->container['parsley.broker'];

        // if task registered - prepare broker
        // prepare broker?
        // TODO: local call support

        $broker->send($payload);

        $this->fireSegmentEvent('sent', [$payload]);
    }

    /**
     * @param SegmentInterface      $segment
     * @param \Parsley\Core\Payload $payload
     */
    public function onPayloadResend($segment, $payload)
    {
        $this->fireSegmentEvent('resend', [$payload]);

        /** @var \Parsley\Plugins\Brokers\BrokerInterface $broker */
        $broker = $this->container['parsley.broker'];

        $delivery = $payload->getDelivery();
//        $properties = $payload->getProperties();
//        $headers    = $properties->getHeaders();

//        // TODO: move this logic to separate plugin, first time just resend (and if resend properly count attempts), then modify headers and send as new
//        if (isset($headers['parsley-retries'])) {
//            if ($headers['parsley-retries'] > 0) {
//                // retry policy
//                $headers['parsley-retries']--;
//
//                isset($headers['parsley-attempted'])
//                    ? $headers['parsley-attempted']++
//                    : $headers['parsley-attempted'] = 1;
//
//                $payload->getProperties()->setHeaders($headers);
//                $this->broker->send($payload); // not a real resend, how should backend notified?
//            } else {
//                $this->broker->drop($payload);
//            }
//        } else {
        // standard 1 redelivery if not redelivered

        $resend = $delivery && !$delivery->getEnvelope()->isRedeliver();

        if ($resend) {
            $broker->drop($payload);
        } else {
            $broker->resend($payload); // special case resend, notify backend?
        }

        $this->fireSegmentEvent('resent', [$payload, $resend]);
    }

    /**
     * @param SegmentInterface      $segment
     * @param \Parsley\Core\Payload $payload
     */
    public function onPayloadDrop($segment, $payload)
    {
        $this->fireSegmentEvent('drop', [$payload]);

        /** @var \Parsley\Plugins\Brokers\BrokerInterface $broker */
        $broker = $this->container['parsley.broker'];

        $broker->drop($payload);

        $this->fireSegmentEvent('dropped', [$payload]);
    }

    /**
     * @param SegmentInterface      $segment
     * @param \Parsley\Core\Payload $payload
     */
    public function onPayloadReceive($segment, $payload)
    {
        $this->fireSegmentEvent('receive', [$payload]);

        /** @var \Parsley\Core\TasksPool $tasks_pool */
        $tasks_pool = $this->container['parsley.tasks_pool'];

        $task_name = $payload->getName();

        // if not registered - drop it, because it should not be here at all

        $found = $tasks_pool->isRegistered($payload->getName());

        if ($found) {
            $this->fireSegmentEvent("known.payload.received: {$task_name}", [$payload, $found]);
        } else {
            $this->fireSegmentEvent("unknown.payload.received: {$task_name}", [$payload, $found]);
        }

        $this->fireSegmentEvent('received', [$payload, $found]);
    }
}