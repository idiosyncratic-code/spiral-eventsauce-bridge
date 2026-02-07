<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use Enqueue\ConnectionFactoryFactory;
use Enqueue\Sns\SnsDestination;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;

final class EnqueueMessageDispatcherConfig implements AsyncMessageDispatcherConfig
{
    public function __construct(
        private readonly string $destination,
        private readonly string $dsn,
        private readonly string $awsKey,
        private readonly string $awsSecret,
        private readonly string $region,
        private readonly string $endpoint,
    ) {
    }

    public function createProducer(
        MessageSerializer $serializer,
    ) : MessageDispatcher {
        $context = (new ConnectionFactoryFactory())->create([
            'dsn' => $this->dsn,
            'key' => $this->awsKey,
            'secret' => $this->awsSecret,
            'region' => $this->region,
            'endpoint' => $this->endpoint,
        ])->createContext();

        $destination = $context->createTopic($this->destination);

        if ($destination instanceof SnsDestination) {
            $destination->setFifoTopic(true);

            $destination->setContentBasedDeduplication(true);
        }

        return new EnqueueMessageDispatcher(
            $serializer,
            $context,
            $destination,
        );
    }

    /** @param array<MessageConsumer> $consumers */
    public function createConsumer(
        MessageSerializer $serializer,
        array $consumers,
    ) : MessageDispatcher {
        $context = (new ConnectionFactoryFactory())->create([
            'dsn' => $this->dsn,
            'key' => $this->awsKey,
            'secret' => $this->awsSecret,
            'region' => $this->region,
            'endpoint' => $this->endpoint,
        ])->createContext();

        return new EnqueueMessageDispatcher(
            $serializer,
            $context,
            $context->createTopic($this->destination),
        );
    }
}
