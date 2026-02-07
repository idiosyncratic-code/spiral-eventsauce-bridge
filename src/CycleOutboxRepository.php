<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use ArrayIterator;
use Cycle\Database\DatabaseInterface;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\MessageOutbox\OutboxRepository;
use Traversable;

use function array_fill;
use function array_map;
use function assert;
use function count;
use function implode;
use function is_int;
use function is_string;
use function json_decode;
use function json_encode;

final class CycleOutboxRepository implements OutboxRepository
{
    public const string CYCLE_OUTBOX_MESSAGE_ID = '__cycle_outbox.message_id';

    public function __construct(
        private readonly DatabaseInterface $database,
        private string $tableName,
        private MessageSerializer $serializer,
    ) {
    }

    public function persist(
        Message ...$messages,
    ) : void {
        $numberOfMessages = count($messages);

        if ($numberOfMessages === 0) {
            return;
        }

        $sqlQuery = 'INSERT INTO ' . $this->tableName . ' (payload) VALUES ' . implode(', ', array_fill(0, $numberOfMessages, '(?)'));

        $serializedMessages = [];
        foreach ($messages as $message) {
            $serializedMessages[] = json_encode($this->serializer->serializeMessage($message));
        }

        $this->database->execute($sqlQuery, $serializedMessages);
    }

    /** @return ArrayIterator<int, Message> */
    public function retrieveBatch(
        int $batchSize,
    ) : Traversable {
        $sqlQuery = 'SELECT id, payload FROM ' . $this->tableName . ' WHERE consumed = FALSE ORDER BY id ASC LIMIT ? ';
        $statement = $this->database->query($sqlQuery, [$batchSize]);

        while ($row = $statement->fetch()) {
            $message = $this->serializer->unserializePayload(json_decode($row['payload'], true));

            yield $message->withHeader(self::CYCLE_OUTBOX_MESSAGE_ID, (int) $row['id']);
        }
    }

    public function markConsumed(
        Message ...$messages,
    ) : void {
        if (count($messages) === 0) {
            return;
        }

        $ids = array_map(
            function (Message $message) {
                return $this->idFromMessage($message);
            },
            $messages,
        );

        $sqlStatement = 'UPDATE ' . $this->tableName . ' SET consumed = TRUE WHERE id IN (:ids)';

        $this->database->execute($sqlStatement, [
            'ids' => implode(',', $ids),
        ]);
    }

    public function deleteMessages(
        Message ...$messages,
    ) : void {
        if (count($messages) === 0) {
            return;
        }

        $ids = array_map(
            function (Message $message) {
                return $this->idFromMessage($message);
            },
            $messages,
        );

        $sqlStatement = 'DELETE FROM ' . $this->tableName . ' WHERE id IN (:ids)';

        $this->database->execute($sqlStatement, [
            'ids' => implode(',', $ids),
        ]);
    }

    public function cleanupConsumedMessages(
        int $amount,
    ) : int {
        $sqlStatement = 'DELETE FROM ' . $this->tableName . ' WHERE consumed = TRUE LIMIT ?';

        return (int) $this->database->execute($sqlStatement, [$amount]);
    }

    public function numberOfMessages() : int
    {
        $statement = $this->database->query('SELECT COUNT(id) FROM ' . $this->tableName);
        $row = $statement->fetch();

        return (int) $row[0];
    }

    public function numberOfConsumedMessages() : int
    {
        $statement = $this->database->query('SELECT COUNT(id) FROM ' . $this->tableName . ' WHERE consumed = TRUE');
        $row = $statement->fetch();

        return (int) $row[0];
    }

    public function numberOfPendingMessages() : int
    {
        $statement = $this->database->query('SELECT COUNT(id) FROM ' . $this->tableName . ' WHERE consumed = FALSE');
        $row = $statement->fetch();

        return (int) $row[0];
    }

    private function idFromMessage(
        Message $message,
    ) : int {
        $id = $message->header(self::CYCLE_OUTBOX_MESSAGE_ID);
        assert(is_int($id) || is_string($id));

        return (int) $id;
    }
}
