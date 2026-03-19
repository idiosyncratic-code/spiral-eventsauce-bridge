<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use Cycle\Database\DatabaseInterface;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\MessageOutbox\OutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\CycleOutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use ReflectionClass;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Stringable;

use function is_string;
use function sprintf;

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
        if (is_string($context) === false) {
            throw new RuntimeException(sprintf(
                'Cannot create instance of %s',
                OutboxRepository::class,
            ));
        }

        $domain = $this->config->domain($context);

        $database = $this->factory->make(
            DatabaseInterface::class,
            context: $domain['database'],
        );

        return new CycleOutboxRepository(
            $database,
            $database->table($domain['outbox']['tableName'])->getName(),
            $this->serializer,
        );
    }
}
