<?php


namespace Parsley\Plugins;


trait SegmentTrait
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    protected $type = 'segment';
    protected $name;


//    public function __construct(Container $container)
//    {
//        $this->container = $container;
//
//        $this->name = get_class($this);
//    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Return array of events and their handlers (with optional priority).
     *
     * @return array
     */
    public function handles()
    {
        return [];
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $this->fireSegmentEvent('subscribe', [$events]);

        foreach ($this->handles() as $handle) {
            switch (count($handle)) {
                case 2:
                    $events->listen($handle[0], $handle[1]);
                    break;
                case 3:
                    $events->listen($handle[0], $handle[1], $handle[2]);
                    break;
            }
        }

        $this->fireSegmentEvent('subscribed', [$events]);
    }

    protected function fireSegmentEvent($event, $payload = array(), $halt = false)
    {
        $event = "parsley.{$this->getType()}: {$this->getName()}, {$event}";

        array_unshift($payload, $this);

        return $this->container['events']->fire($event, $payload, $halt);
    }
} 