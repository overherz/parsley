<?php


namespace Parsley\Examples\Tasks;

use Parsley\Core\Payload;
use Parsley\Core\Task;

class HiUniverseTask extends Task
{
    protected $arguments_order = ['hi', 'universe'];

    public function callback($hi, $universe) // <<< TODO: should be the only valid source for arguments and their default values
    {
        echo 'Callback ' , $this->getName(), " called with hi: '{$hi}' and universe: '{$universe}'", PHP_EOL;

        /** @var Payload $payload */
        $payload = $this->container->make('parsley.payload');

        print_r($payload->getAttributes());
        echo PHP_EOL;
        print_r(array_filter($payload->getHeaders()));
        echo PHP_EOL;
    }
}