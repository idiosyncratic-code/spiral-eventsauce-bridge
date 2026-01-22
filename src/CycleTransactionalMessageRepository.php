<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Cycle\Database\DatabaseInterface;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\PaginationCursor;
use EventSauce\EventSourcing\UnableToPersistMessages;
use EventSauce\MessageOutbox\OutboxRepository;
use Generator;
use Throwable;

final class CycleTransactionalMessageRepository implements MessageRepository
{
    public function __construct(
        private readonly DatabaseInterface $database,
        private MessageRepository $messageRepository,
        private OutboxRepository $outboxRepository,
    ) {
    }

    public function persist(
        Message ...$messages,
    ) : void {
        try {
            $this->database->begin();

            try {
                $this->messageRepository->persist(...$messages);
                $this->outboxRepository->persist(...$messages);
                $this->database->commit();
            } catch (Throwable $exception) {
                $this->database->rollBack();

                throw $exception;
            }
        } catch (Throwable $exception) {
            throw UnableToPersistMessages::dueTo('', $exception);
        }
    }

    public function retrieveAll(
        AggregateRootId $id,
    ) : Generator {
        return $this->messageRepository->retrieveAll($id);
    }

    public function retrieveAllAfterVersion(
        AggregateRootId $id,
        int $aggregateRootVersion,
    ) : Generator {
        return $this->messageRepository->retrieveAllAfterVersion($id, $aggregateRootVersion);
    }

    public function paginate(
        PaginationCursor $cursor,
    ) : Generator {
        return $this->messageRepository->paginate($cursor);
    }
}
