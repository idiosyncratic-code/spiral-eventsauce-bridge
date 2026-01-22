<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class MessageRepositoryTable
{
    public function __construct(
        public readonly string|null $table = null,
        public readonly string $database = 'default',
        public readonly bool $useOutbox = false,
        public readonly string $outboxTableName = 'message_outbox',
    ) {
    }
}
