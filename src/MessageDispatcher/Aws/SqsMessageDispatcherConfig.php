<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws;

use Aws\Sqs\SqsClient;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageConsumer;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;

final class SqsMessageDispatcherConfig implements AsyncMessageDispatcherConfig
{
    public function __construct(
        private readonly string $queue,
        private readonly string $awsKey,
        private readonly string $awsSecret,
        private readonly string $region,
        private readonly string|null $endpoint,
    ) {
    }

    public function createProducer(
        MessageSerializer $serializer,
    ) : MessageDispatcher {
        $client = new SqsClient([
            'credentials' => [
                'key' => $this->awsKey,
                'secret' => $this->awsSecret,
            ],
            'region' => $this->region,
            'endpoint' => $this->endpoint,
        ]);

        return new SqsMessageDispatcher(
            $serializer,
            $client,
            $this->queue,
        );
    }

    public function createConsumer(
        MessageSerializer $serializer,
        MessageConsumer ...$consumers,
    ) : AsyncMessageConsumer {
        $client = new SqsClient([
            'credentials' => [
                'key' => $this->awsKey,
                'secret' => $this->awsSecret,
            ],
            'region' => $this->region,
            'endpoint' => $this->endpoint,
        ]);

        return new SqsMessageConsumer(
            $client,
            $serializer,
            $this->queue,
            new SynchronousMessageDispatcher(...$consumers),
        );
    }
}
