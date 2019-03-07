<?php

return [
    'amqpy'      => [
        // 'credentials'   => [], // default
        // 'exchange'      => 'parsley', // default
        // 'exchange_type' => 'topic', // default
        // 'queue'         => 'parsley', // default
        'serializers' => [
            'AMQPy\Serializers\JSON',
            'AMQPy\Serializers\PhpNative',
            'AMQPy\Serializers\PlainText',
        ],
        'qos' => 1,
    ],
    'priorities' => [
        0 => 'low', 1 => 'low', 2 => 'low', 3 => 'low', 4 => 'low',
        5 => 'high', 6 => 'high', 7 => 'high', 8 => 'high', 9 => 'high',
    ],
    'plugins'    => [
        'Parsley\Plugins\Core\KernelPlugin',
        'Parsley\Plugins\Core\PayloadCheckerPlugin',
        'Parsley\Plugins\Core\PayloadHandlerPlugin',
        'Parsley\Plugins\Core\TasksGroupPlugin',
        'Parsley\Plugins\Core\TaskNotifierPlugin',
        'Parsley\Plugins\Brokers\RabbitMQ',
    ],
    'tasks'      => [
        'Parsley\Examples\Tasks\CallOtherTask',
        'Parsley\Examples\Tasks\HelloWorldTask',
        'Parsley\Examples\Tasks\HiUniverseTask',
        'Parsley\Examples\Tasks\AsyncGroupTask',
    ],
    'payload'    => [
        // 'content_type' => 'application/json', // default
    ]
];