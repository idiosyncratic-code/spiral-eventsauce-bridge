<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use Enqueue\ConnectionFactoryFactory;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Psr\Container\ContainerInterface;

final class EnqueueMessageDispatcherConfig extends MessageDispatcherConfig
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly array $config,
        private readonly string $destination,
    ) {
    }

    public function create(
        ContainerInterface $container,
        MessageConsumer ...$consumers,
    ) : MessageDispatcher {
        $context = (new ConnectionFactoryFactory())->create($this->config)->createContext();

        return new EnqueueMessageDispatcher(
            $container->get(MessageSerializer::class),
            $context,
            $context->createTopic($destination),
        );
    }
}
