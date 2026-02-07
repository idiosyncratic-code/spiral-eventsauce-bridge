<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use Enqueue\Sns\SnsMessage;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use Interop\Queue\Producer;

use function array_map;
use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_OBJECT_AS_ARRAY;

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
            $serializedMessage = json_decode(
                json: json_encode($this->serializer->serializeMessage($message)),
                flags: JSON_OBJECT_AS_ARRAY,
            );

            $message = $this->context->createMessage(
                json_encode($serializedMessage['payload']),
                [],
            );

            $messageAttributes = array_map(static function ($attribute) {
                return [
                    'DataType' => 'String',
                    'StringValue' => (string) $attribute,
                ];
            }, $serializedMessage['headers']);

            if ($message instanceof SnsMessage) {
                $message->setMessageAttributes($messageAttributes);

                $message->setMessageGroupId(sprintf(
                    '%s-%s',
                    $serializedMessage['headers']['__aggregate_root_type'],
                    $serializedMessage['headers']['__aggregate_root_id'],
                ));
            }

            $this->producer->send(
                $this->destination,
                $message,
            );
        }
    }
}
