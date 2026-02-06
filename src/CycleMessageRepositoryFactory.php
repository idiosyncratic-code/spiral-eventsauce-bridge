<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\IdEncoding\IdEncoder;
use EventSauce\IdEncoding\StringIdEncoder;
use EventSauce\MessageOutbox\OutboxRepository;
use EventSauce\MessageRepository\TableSchema\DefaultTableSchema;
use EventSauce\MessageRepository\TableSchema\TableSchema;

use function sprintf;

final class CycleMessageRepositoryFactory
{
    private readonly TableSchema $tableSchema;

    private readonly IdEncoder $aggregateRootIdEncoder;

    private readonly IdEncoder $eventIdEncoder;

    /** @var array<string, CycleMessageRepository> */
    private array $repositories = [];

    public function __construct(
        private readonly DatabaseProviderInterface $dbProvider,
        private readonly MessageSerializer $serializer,
        private readonly int $jsonEncodeOptions = 0,
        TableSchema|null $tableSchema = null,
        IdEncoder|null $aggregateRootIdEncoder = null,
        IdEncoder|null $eventIdEncoder = null,
    ) {
        $this->tableSchema = $tableSchema ?? new DefaultTableSchema();
        $this->aggregateRootIdEncoder = $aggregateRootIdEncoder ?? new StringIdEncoder();
        $this->eventIdEncoder = $eventIdEncoder ?? $this->aggregateRootIdEncoder;
    }

    public function makeMessageRepository(
        string $db,
        string $table,
        OutboxRepository|null $outboxRepository,
    ) : CycleMessageRepository|CycleTransactionalMessageRepository {
        $repositoryKey = sprintf('%s_%s', $db, $table);

        if (isset($this->repositories[$repositoryKey])) {
            return $this->repositories[$repositoryKey];
        }

        $database = $this->dbProvider->database($db);

        $repository = new CycleMessageRepository(
            table: $database->table(sprintf('%s_event_store', $table)),
            serializer: $this->serializer,
            jsonEncodeOptions: $this->jsonEncodeOptions,
            tableSchema: $this->tableSchema,
            aggregateRootIdEncoder: $this->aggregateRootIdEncoder,
            eventIdEncoder: $this->eventIdEncoder,
        );

        if ($outboxRepository !== null) {
            $repository = new CycleTransactionalMessageRepository(
                $database,
                $repository,
                $outboxRepository,
            );
        }

        return $this->repositories[$repositoryKey] = $repository;
    }

    private function makeOutboxRepository(
        DatabaseInterface $database,
        string $outboxTableName,
        MessageSerializer $serializer,
    ) : OutboxRepository {
        return new CycleOutboxRepository(
            $database,
            $database->table($outboxTableName)->getName(),
            $serializer,
        );
    }
}
