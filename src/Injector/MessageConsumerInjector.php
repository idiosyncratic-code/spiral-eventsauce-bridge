<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageConsumer;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

use function array_map;
use function is_string;

/** @implements InjectorInterface<AsyncMessageConsumer> */
final class MessageConsumerInjector implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EventSauceConfig $config,
    ) {
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : AsyncMessageConsumer {
        if (is_string($context) === false) {
            throw new RuntimeException('Cannot create MessageConsumer');
        }

        $dispatcher = $this->config->dispatcher($context);

        if (
            isset($dispatcher['receiver']) &&
            $dispatcher['receiver'] !== true
        ) {
            throw new RuntimeException('Cannot create MessageConsumer');
        }

        if (! $dispatcher['driver'] instanceof AsyncMessageDispatcherConfig) {
            throw new RuntimeException('Cannot create MessageConsumer');
        }

        $consumers = array_map(function ($consumer) {
            return $this->container->get($consumer);
        }, $dispatcher['consumers']);

        return $dispatcher['driver']->createConsumer(
            $this->container->get(MessageSerializer::class),
            ...$consumers,
        );
    }
}
