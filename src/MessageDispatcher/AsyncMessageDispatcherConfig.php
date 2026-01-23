<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use Psr\Container\ContainerInterface;

interface AsyncMessageDispatcherConfig
{
    public static function createProducer(
        ContainerInterface $container,
        array $config,
    ) : MessageDispatcher;

    public static function createConsumer(
        ContainerInterface $container,
        array $config,
        array $consumers,
    ) : MessageDispatcher;
}
