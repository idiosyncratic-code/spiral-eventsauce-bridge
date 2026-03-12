<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws;

use Aws\Sns\SnsClient;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageConsumer;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use RuntimeException;

final class SnsMessageDispatcherConfig implements AsyncMessageDispatcherConfig
{
    public function __construct(
        private readonly string $topic,
        private readonly string $awsKey,
        private readonly string $awsSecret,
        private readonly string $region,
        private readonly string|null $endpoint,
        private readonly SqsMessageDispatcherConfig|null $consumerConfig = null,
    ) {
    }

    public function createProducer(
        MessageSerializer $serializer,
    ) : MessageDispatcher {
        $clientParams = [
            'credentials' => [
                'key' => $this->awsKey,
                'secret' => $this->awsSecret,
            ],
            'region' => $this->region,
        ];

        if ($this->endpoint !== null) {
            $clientParams['endpoint'] = $this->endpoint;
        }

        $client = new SnsClient($clientParams);

        return new SnsMessageDispatcher(
            $serializer,
            $client,
            $this->topic,
        );
    }

    public function createConsumer(
        MessageSerializer $serializer,
        MessageConsumer ...$consumers,
    ) : AsyncMessageConsumer {
        if ($this->consumerConfig === null) {
            throw new RuntimeException('No consumer configured');
        }

        return $this->consumerConfig->createConsumer($serializer, ...$consumers);
    }
}
