<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;
use Ramsey\Uuid\Uuid;

final class EventIdHeaderDecorator implements MessageDecorator
{
    public function decorate(
        Message $message,
    ) : Message {
        return $message->withHeader(
            Header::EVENT_ID,
            Uuid::uuid7()->toString(),
        );
    }
}
