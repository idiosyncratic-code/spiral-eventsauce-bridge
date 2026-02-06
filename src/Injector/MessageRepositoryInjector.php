<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\MessageRepository;
use EventSauce\MessageOutbox\OutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageRepositoryTable;
use Idiosyncratic\Spiral\EventSauceBridge\CycleMessageRepositoryFactory;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

final class MessageRepositoryInjector implements InjectorInterface
{
    public function __construct(
        private readonly CycleMessageRepositoryFactory $factory,
        private readonly OutboxRepository|null $outboxRepository,
        private readonly EventSauceConfig $config,
    ) {
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : MessageRepository {
        if (! $context instanceof ReflectionParameter) {
            throw new RuntimeException('not a reflection parameter');
        }

        $tableAttribute = $context->getAttributes(MessageRepositoryTable::class)[0]?->newInstance();

        if (! $tableAttribute instanceof MessageRepositoryTable) {
            throw new RuntimeException('no message repository table attribute');
        }

        $database = $tableAttribute->database;

        $table = $tableAttribute->table;

        return $this->factory->makeMessageRepository(
            $tableAttribute->database,
            $tableAttribute->table,
            $this->config->outboxEnabled() ? $this->outboxRepository : null,
        );
    }
}
