<?php

include 'start.php';

$pool = $container['parsley.tasks_pool'];

/** @var Parsley\Examples\Tasks\CallOtherTask $task */
$task = $container['parsley.tasks_pool']->get('Parsley.Examples.Tasks.CallOther');

$tasks = [
    '' => ['hello', 'world'],
    'Parsley.Examples.Tasks.HiUniverse' => ['hi', 'universe'],
];

$no = 0;

while (true) {
    $no++;

    $call_task = array_rand($tasks);
    $call_args = $tasks[$call_task];

    $call_args = array_combine(['hello', 'world'], [date("Y-m-d H:i:s"), $no]);

    $payload = new \Parsley\Core\Payload('Parsley.Examples.Tasks.HelloWorld', $call_args, new \AMQPy\Client\Properties());

    if (isset($argv[1]) && $argv[1] == 'high') {
        $payload->getProperties()->setPriority(9);
    }

    $payload->getProperties()->setPriority(9);

    $app->send($payload);

    die;
    sleep(1);
}