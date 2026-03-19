<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Injector;

use EventSauce\EventSourcing\MessageRepository;
use EventSauce\MessageOutbox\OutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageRepositoryTable;
use Idiosyncratic\Spiral\EventSauceBridge\CycleMessageRepositoryFactory;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Stringable;

/** @implements InjectorInterface<MessageRepository> */
final class MessageRepositoryInjector implements InjectorInterface
{
    public function __construct(
        private readonly CycleMessageRepositoryFactory $repositoryFactory,
        private readonly FactoryInterface $factory,
    ) {
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : MessageRepository {
        if (! $context instanceof ReflectionParameter) {
            throw new RuntimeException('not a reflection parameter');
        }

        $tableAttribute = $context->getAttributes(MessageRepositoryTable::class)[0]->newInstance();

        $database = $tableAttribute->database;

        $table = $tableAttribute->table;

        $useOutbox = $tableAttribute->useOutbox;

        $outboxTableName = $tableAttribute->outboxTableName;

        $domain = $tableAttribute->domain;

        $outboxRepository = null;

        if ($useOutbox === true) {
            $outboxRepository = $this->factory->make(
                OutboxRepository::class,
                context: $domain,
            );
        }

        return $this->repositoryFactory->makeMessageRepository(
            $database,
            $table,
            $outboxRepository,
        );
    }
}
