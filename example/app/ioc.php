<?php

/** @var \Illuminate\Container\Container $container */

$container->bind(
          'parsley.application', function ($container) {

                  /** @var \Illuminate\Container\Container $container */

                  $application = new \Parsley\Application($container);

                  $plugins = $container['config']->get('parsley.plugins', []);

                  foreach ($plugins as $plugin) {
                      $p = $container->make($plugin);
                      $container->instance($plugin, $p);

                      $application->registerPlugin($p);
                  }

                  return $application;
              },
          true
);

$container->bind('parsley.broker', 'Parsley\Plugins\Brokers\RabbitMQ', true);

$container->bind(
          'parsley.tasks_pool', function ($container) {
                  $tasks_pool = new \Parsley\Core\TasksPool($container);

                  $tasks = $container['config']->get('parsley.tasks', []);

                  $tasks_pool->register($tasks);

                  return $tasks_pool;

              },
          true
);

$container->bind('parsley.payload_builder', 'Parsley\Helpers\PayloadBuilder', true);

// AMQPy part

$container->bind('parsley.brokers.rabbitmq.publisher', 'AMQPy\Publisher', true);
$container->bind('parsley.brokers.rabbitmq.listener', 'AMQPy\Listener', true);

$container->bind(
          'AMQPy\Serializers\SerializersPool',
              function ($container) {
                  $serializers_pool = new \AMQPy\Serializers\SerializersPool();
                  $serializers_pool->register($container['config']->get('parsley.amqpy.serializers', []));

                  return $serializers_pool;
              },
          true
);

$container->bind(
          'AMQPConnection',
              function ($container) {
                  $connection = new AMQPConnection($container['config']->get('parsley.amqpy.credentials', []));
                  $connection->connect();

                  return $connection;
              },
          true
);

$container->bind(
          'AMQPChannel', function ($container) {
                  $ch = new AMQPChannel($container->make('AMQPConnection'));

                  $qos = $container['config']->get('parsley.amqpy.qos');

                  if ($qos != null) {
                      $ch->setPrefetchCount($qos);
                  }

                  return $ch;
              },
          true
);

$container->bind(
          'AMQPExchange',
              function ($container) {
                  /** @var AMQPExchange $exchange */
                  $exchange = new AMQPExchange($container->make('AMQPChannel'));

                  $exchange->setName($container['config']->get('parsley.amqpy.exchange', 'parsley'));
                  $exchange->setType($container['config']->get('parsley.amqpy.exchange_type', AMQP_EX_TYPE_TOPIC));
                  $exchange->declareExchange();

                  return $exchange;
              },
          true
);

$container->bind(
          'AMQPQueue',
              function ($container) {
                  /** @var AMQPQueue $queue */
                  $queue = new AMQPQueue($container->make('AMQPChannel'));

                  /** @var \Illuminate\Config\Repository $config */
                  $config = $container['config'];

                  $name = $config->get('parsley.amqpy.queue', 'parsley') . '.' . $config->get('parsley.priority', 'default');

                  $queue->setName($name);
                  $queue->declareQueue();

                  return $queue;
              },
          true
);
