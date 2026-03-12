<?php

declare(strict_types=1);

use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws\SnsMessageDispatcherConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws\SqsMessageDispatcherConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\SyncMessageDispatcherConfig;

return [
    'inflectorClassMap' => [],
    'dispatchers' => [
        'sync' => [
            'driver' => 'sync',
            'consumers' => [],
            'aggregates' => [],
        ],
        'sns' => [
            'driver' => 'sns',
            'consumers' => [],
            'aggregates' => [],
        ],
        'sqs' => [
            'driver' => 'sqs',
            'receiver' => true,
            'consumers' => [],
            'aggregates' => [],
        ],
    ],
    'drivers' => [
        'sync' => new SyncMessageDispatcherConfig(),
        'sns' => new SnsMessageDispatcherConfig(
            topic: env('EVENTSAUCE_DRIVER_SNS_TOPIC'),
            awsKey: env('EVENTSAUCE_AWS_KEY', env('AWS_KEY')),
            awsSecret: env('EVENTSAUCE_AWS_SECRET', env('AWS_SECRET')),
            region: env('EVENTSAUCE_AWS_REGION', env('AWS_REGION')),
            endpoint: env('EVENTSAUCE_AWS_ENDPOINT', env('AWS_ENDPOINT')),
        ),
        'sqs' => new SqsMessageDispatcherConfig(
            queue: env('EVENTSAUCE_DRIVER_SQS_QUEUE'),
            awsKey: env('EVENTSAUCE_AWS_KEY', env('AWS_KEY')),
            awsSecret: env('EVENTSAUCE_AWS_SECRET', env('AWS_SECRET')),
            region: env('EVENTSAUCE_AWS_REGION', env('AWS_REGION')),
            endpoint: env('EVENTSAUCE_AWS_ENDPOINT', env('AWS_ENDPOINT')),
        ),
    ],
    'aggregateRoots' => [],
    'outbox' => [
        'enabled' => env('EVENTSAUCE_OUTBOX_ENABLED', false),
        'tableName' => env('EVENTSAUCE_OUTBOX_TABLENAME', 'message_outbox'),
        'database' => env('EVENTSAUCE_OUTBOX_DATABASE', null),
        'batchSize' => env('EVENTSAUCE_OUTBOX_BATCHSIZE', 100),
        'commitSize' => env('EVENTSAUCE_OUTBOX_COMMITSIZE', 1),
    ],
];
