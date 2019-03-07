<?php


namespace Parsley\Plugins;


use Illuminate\Container\Container;


interface SegmentInterface
{

    public function __construct(Container $container);

    public function getName();

//    public function register();

    // TODO: add dependencies
    // TODO: add overrides/replaces
    // TODO: add conflicts
    // TODO: add suggestions

    /**
     * Return array of events and their handlers (with optional priority).
     *
     * @return array
     */
    public function handles();

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events);
}