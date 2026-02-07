<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\MessageConsumer;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;

interface AsyncMessageDispatcherConfig
{
    public function createProducer(
        MessageSerializer $serializer,
    ) : MessageDispatcher;

    /** @param array<MessageConsumer> $consumers */
    public function createConsumer(
        MessageSerializer $serializer,
        array $consumers,
    ) : MessageDispatcher;
}
