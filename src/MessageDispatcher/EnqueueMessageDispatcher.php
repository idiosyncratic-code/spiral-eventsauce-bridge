<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use Interop\Queue\Producer;

use function json_decode;
use function json_encode;

final class EnqueueMessageDispatcher implements MessageDispatcher
{
    private Producer $producer;

    public function __construct(
        private readonly MessageSerializer $serializer,
        private readonly Context $context,
        private readonly Destination $destination,
    ) {
        $this->producer = $this->context->createProducer();
    }

    public function dispatch(
        Message ...$messages,
    ) : void {
        foreach ($messages as $message) {
            $serializedMessage = json_decode(json_encode($this->serializer->serializeMessage($message)));

            $this->producer->send(
                $this->destination,
                $this->context->createMessage($serializedMessage->payload->to_payload, [], $serializedMessage->headers),
            );
        }
    }
}
