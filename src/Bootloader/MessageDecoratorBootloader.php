<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDecorator as MessageDecoratorAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

use function count;

final class MessageDecoratorBootloader extends Bootloader implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageDecorator::class, self::class);
    }

    #[BindMethod]
    public function createMessageDefaultHeadersDecorator(
        ClassNameInflector $inflector,
    ) : DefaultHeadersDecorator {
        return new DefaultHeadersDecorator(
            inflector: $inflector,
        );
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : MessageDecorator {
        if ($context === null) {
            return $this->container->get(DefaultHeadersDecorator::class);
        }

        if (! $context instanceof ReflectionParameter) {
            throw new RuntimeException(sprintf('%s is not a reflection parameter', $context));
        }

        $decoratorAttributes = $context->getAttributes(MessageDecoratorAttribute::class);

        $decorators = [];

        foreach ($decoratorAttributes as $decoratorAttribute) {
            $decoratorClass = $decoratorAttribute->newInstance()->name;

            $decorators[] = $this->container->get($decoratorClass);
        }

        if (count($decorators) === 0) {
            return $this->container->get(DefaultHeadersDecorator::class);
        }

        if (count($decorators) > 1) {
            return new MessageDecoratorChain(...$decorators);
        }

        return $decorators[0];
    }
}
