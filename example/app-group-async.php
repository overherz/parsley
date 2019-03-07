<?php

include 'start.php';

$pool = $container['parsley.tasks_pool'];

/** @var Parsley\Examples\Tasks\AsyncGroupTask $task */
$task = $container['parsley.tasks_pool']->get('Parsley.Examples.Tasks.AsyncGroup');

$tasks = [
    'Parsley.Examples.Tasks.HelloWorld',
    'Parsley.Examples.Tasks.HiUniverse',
];

$no = 0;

while (true) {
    $no++;

    $args = [
        ['hello' => date("Y-m-d H:i:s"), 'world' => $no],
        ['hi' => date("Y-m-d H:i:s"), 'universe' => $no],
    ];

    $task->schedule($tasks, $args);
    die;
    sleep(1);
}
