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

    public function createConsumer(
        MessageSerializer $serializer,
        MessageConsumer ...$consumers,
    ) : AsyncMessageConsumer;
}
