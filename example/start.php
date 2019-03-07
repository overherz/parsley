<?php

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require $file;

$loader->add('Parsley\Examples', __DIR__);

$container = new \Illuminate\Container\Container();
$container->instance('Illuminate\Container\Container', $container);


$config = new \Illuminate\Config\Repository(
    new \Illuminate\Config\FileLoader(new \Illuminate\Filesystem\Filesystem(), __DIR__ . '/app/config'),
    'example'
);

$container->instance('config', $config);
$container->bind('events', 'Illuminate\Events\Dispatcher', true);

/** @var Illuminate\Events\Dispatcher $events */
$events = $container['events'];

//$listeners = $events->getListeners('parsley.application: ds2, payload.send');

//$events->listen(
//       '*', function () use ($events) {
//               echo '- Firing ', $events->firing(), PHP_EOL;
//           }
//);

//$events->listen(
//       'parsley.application: *', function () use ($events) {
//               echo ' -- Application fires ', $events->firing(), PHP_EOL;
//           }
//);
//
//$events->listen(
//       'parsley.plugin: *', function () use ($events) {
//               echo '   -- Plugin fires ', $events->firing(), PHP_EOL;
//           }
//);

include __DIR__ . '/app/ioc.php';

$app = $container['parsley.application'];



//var_dump($listeners);
//die;

//var_dump($container['events']);
