<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDispatcher as MessageDispatcherAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

use function count;

final class MessageDispatcherBootloader extends Bootloader implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageDispatcher::class, self::class);
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : MessageDispatcher {
        if (! $context instanceof ReflectionParameter) {
            throw new RuntimeException(sprintf('%s is not a reflection parameter', $context));
        }

        $dispatcherAttributes = $context->getAttributes(MessageDispatcherAttribute::class);

        $dispatcherNames = [];

        foreach ($dispatcherAttributes as $dispatcherAttribute) {
            $dispatcherNames[] = $dispatcherAttribute->newInstance()->name;
        }

        if (count($dispatcherNames) === 0) {
            return new SynchronousMessageDispatcher();
        }

        $dispatchers = [];

        $config = $this->container->get(EventSauceConfig::class);

        foreach ($dispatcherNames as $dispatcherName) {
            $dispatcher = $config->getDispatcher($dispatcherName);

            $consumers = [];

            foreach ($dispatcher['consumers'] as $consumer) {
                $consumers[] = $this->container->get($consumer);
            }

            $dispatchers[] = $dispatcher['config']->create($this->container, ...$consumers);
        }

        if (count($dispatchers) > 1) {
            return new MessageDispatcherChain(...$dispatchers);
        }

        return $dispatchers[0];
    }
}
