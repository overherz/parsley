<?php

include 'start.php';

/** @var Parsley\Application $app */
$app = $container['parsley.application'];

$app->start(isset($argv[1]) ? $argv[1] : 'default');

//var_dump($events);