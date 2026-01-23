<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use Enqueue\ConnectionFactoryFactory;
use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class EnqueueMessageDispatcherConfig implements AsyncMessageDispatcherConfig
{
    public static function createProducer(
        ContainerInterface $container,
        array $config,
    ) : MessageDispatcher {
        /*
        $consumerInstances = [];

        foreach ($consumers as $consumerClass) {
            $consumerInstances = $container->get($consumerClass);
        }
         */
        $destination = $config['destination'] ?? null;

        if ($destination === null) {
            throw new RuntimeException("No destination provided");
        }

        unset($config['destination']);

        $context = (new ConnectionFactoryFactory())->create($config)->createContext();

        return new EnqueueMessageDispatcher(
            $container->get(MessageSerializer::class),
            $context,
            $context->createTopic($destination),
        );
    }

    public static function createConsumer(
        ContainerInterface $container,
        array $config,
        array $consumers,
    ) : MessageDispatcher {
        /*
        $consumerInstances = [];

        foreach ($consumers as $consumerClass) {
            $consumerInstances = $container->get($consumerClass);
        }
         */
        $destination = $config['destination'] ?? null;

        if ($destination === null) {
            throw new RuntimeException("No destination provided");
        }

        unset($config['destination']);

        $context = (new ConnectionFactoryFactory())->create($config)->createContext();

        return new EnqueueMessageDispatcher(
            $container->get(MessageSerializer::class),
            $context,
            $context->createTopic($destination),
        );
    }
}
