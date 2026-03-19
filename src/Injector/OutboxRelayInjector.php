<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\MessageOutbox\OutboxRepository;
use EventSauce\MessageOutbox\RelayMessages;
use EventSauce\MessageOutbox\RelayMessagesThroughDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use ReflectionClass;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Stringable;

use function array_shift;
use function count;
use function is_string;
use function sprintf;

/** @implements InjectorInterface<RelayMessages> */
final class OutboxRelayInjector implements InjectorInterface
{
    public function __construct(
        private readonly EventSauceConfig $config,
        private readonly FactoryInterface $factory,
    ) {
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : RelayMessages {
        if (is_string($context) === false) {
            throw new RuntimeException(sprintf(
                'Cannot create instance of %s',
                RelayMessages::class,
            ));
        }

        $outboxRepository = $this->factory->make(
            OutboxRepository::class,
            context: $context,
        );

        $dispatchers = [];

        foreach ($this->config->dispatchers() as $dispatcherName => $dispatcher) {
            if (! ($dispatcher['driver'] instanceof AsyncMessageDispatcherConfig)) {
                continue;
            }

            $dispatchers[] = $this->factory->make(
                MessageDispatcher::class,
                context: $dispatcherName,
            );
        }

        $dispatcher = count($dispatchers) > 1
            ? new MessageDispatcherChain(...$dispatchers)
            : array_shift($dispatchers);

        return new RelayMessagesThroughDispatcher(
            $outboxRepository,
            $dispatcher,
        );
    }
}
