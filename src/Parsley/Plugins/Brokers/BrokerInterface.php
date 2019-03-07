<?php


namespace Parsley\Plugins\Brokers;

use Parsley\Core\Payload;

interface BrokerInterface
{
    public function setup();

    public function start();

    public function stop();

    public function cleanup();

    public function send(Payload $payload);

    public function resend(Payload $payload);

    public function drop(Payload $payload);

    public function receive(Payload $payload);

    // public function respond();
}