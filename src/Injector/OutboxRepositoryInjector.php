<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use Cycle\Database\DatabaseInterface;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\MessageOutbox\OutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\CycleOutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use ReflectionClass;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Stringable;

/** @implements InjectorInterface<OutboxRepository> */
final class OutboxRepositoryInjector implements InjectorInterface
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly EventSauceConfig $config,
        private readonly MessageSerializer $serializer,
    ) {
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : OutboxRepository {
        $database = $this->factory->make(
            DatabaseInterface::class,
            context: $this->config->outboxDatabase(),
        );

        return new CycleOutboxRepository(
            $database,
            $database->table($this->config->outboxTableName())->getName(),
            $this->serializer,
        );
    }
}
