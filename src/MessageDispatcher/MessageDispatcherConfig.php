<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use Psr\Container\ContainerInterface;

abstract class MessageDispatcherConfig
{
    abstract public function create(
        ContainerInterface $container,
        MessageConsumer ...$consumers,
    ) : MessageDispatcher;
}
