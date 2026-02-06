<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDecorator as MessageDecoratorAttribute;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

use function count;
use function sprintf;

final class MessageDecoratorInjector implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
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
