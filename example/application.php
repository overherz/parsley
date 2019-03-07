<?php

include 'start.php';

$pool = $container['parsley.tasks_pool'];

/** @var Parsley\Examples\Tasks\CallOtherTask $task */
$task = $container['parsley.tasks_pool']->get('Parsley.Examples.Tasks.CallOther');

$tasks = [
    'Parsley.Examples.Tasks.HelloWorld' => ['hello', 'world'],
    'Parsley.Examples.Tasks.HiUniverse' => ['hi', 'universe'],
];

$no = 0;

while (true) {
    $no++;

    $call_task = array_rand($tasks);
    $call_args = $tasks[$call_task];

    $call_args = array_combine(['hi', 'universe'], [date("Y-m-d H:i:s"), $no]);

    $task->schedule('Parsley.Examples.Tasks.HelloWorld', $call_args);
    die;
    sleep(1);
}