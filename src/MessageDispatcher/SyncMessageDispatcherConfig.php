<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Psr\Container\ContainerInterface;

final class SyncMessageDispatcherConfig implements MessageDispatcherConfig
{
    public static function create(
        ContainerInterface $container,
        array $config,
        array $consumers,
    ) : MessageDispatcher {
        $instances = [];

        foreach ($consumers as $consumer) {
            $instances[] = $container->get($consumer);
        }
        return new SynchronousMessageDispatcher(...$instances);
    }
}
