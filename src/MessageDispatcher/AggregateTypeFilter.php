<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\AntiCorruptionLayer\MessageFilter;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;

use function in_array;

final class AggregateTypeFilter implements MessageFilter
{
    public function __construct(
        /** @var array<string> */
        private readonly array $allowedAggregateTypes,
    ) {
    }

    public function allows(
        Message $message,
    ) : bool {
        return in_array(
            $message->header(Header::AGGREGATE_ROOT_TYPE),
            $this->allowedAggregateTypes,
            true,
        );
    }
}
