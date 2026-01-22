<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Psr\Container\ContainerInterface;

final class SyncMessageDispatcherConfig extends MessageDispatcherConfig
{
    public function create(
        ContainerInterface $container,
        MessageConsumer ...$consumers,
    ) : MessageDispatcher {
        return new SynchronousMessageDispatcher(...$consumers);
    }
}
