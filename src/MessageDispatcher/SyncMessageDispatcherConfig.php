<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;

final class SyncMessageDispatcherConfig implements MessageDispatcherConfig
{
    /** @param array<MessageConsumer> $consumers */
    public function create(
        array $consumers,
    ) : MessageDispatcher {
        return new SynchronousMessageDispatcher(...$consumers);
    }
}
