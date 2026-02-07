<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDispatcher as MessageDispatcherAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

use function array_map;
use function array_shift;
use function count;
use function is_string;
use function sprintf;

final class MessageDispatcherInjector implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EventSauceConfig $config,
    ) {
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : MessageDispatcher {
        if (is_string($context)) {
            return $this->createDispatcher($context);
        }

        if ($context === null) {
            return $this->createDefaultDispatcher();
        }

        if (! $context instanceof ReflectionParameter) {
            throw new RuntimeException(sprintf('%s is not a reflection parameter', $context));
        }

        $dispatcherAttributes = $context->getAttributes(MessageDispatcherAttribute::class);

        $dispatchers = [];

        foreach ($dispatcherAttributes as $dispatcherAttribute) {
            $dispatchers[] = $this->createDispatcher($dispatcherAttribute->newInstance()->name);
        }

        return count($dispatchers) > 1
            ? new MessageDispatcherChain(...$dispatchers)
            : array_shift($dispatchers);
    }

    private function createDispatcher(
        string|null $dispatcherName,
    ) : MessageDispatcher {
        $dispatcher = $this->config->dispatcher($dispatcherName);

        if ($dispatcher['driver'] instanceof AsyncMessageDispatcherConfig) {
            return $dispatcher['driver']->createProducer(
                $this->container->get(MessageSerializer::class),
            );
        }

        $consumers = array_map(function ($consumer) {
            return $this->container->get($consumer);
        }, $dispatcher['consumers']);

        return $dispatcher['driver']->create(
            $consumers,
        );
    }

    private function createDefaultDispatcher() : MessageDispatcher
    {
        return new SynchronousMessageDispatcher();
    }
}
