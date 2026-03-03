<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDecorator as MessageDecoratorAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\EventIdHeaderDecorator;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

use function array_unshift;
use function count;
use function sprintf;

/** @implements InjectorInterface<MessageDecorator> */
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
            return $this->makeDefaultMessageDecorator();
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
            return $this->makeDefaultMessageDecorator();
        }

        array_unshift($decorators, new EventIdHeaderDecorator());

        return new MessageDecoratorChain(...$decorators);
    }

    private function makeDefaultMessageDecorator() : MessageDecorator
    {
        return new MessageDecoratorChain(
            new EventIdHeaderDecorator(),
            $this->container->get(DefaultHeadersDecorator::class),
        );
    }
}
