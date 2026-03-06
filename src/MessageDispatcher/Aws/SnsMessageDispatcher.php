<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws;

use Aws\Sns\SnsClient;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;

use function array_map;
use function count;
use function json_encode;
use function sprintf;

final class SnsMessageDispatcher implements MessageDispatcher
{
    private string|null $topicArn = null;

    public function __construct(
        private readonly MessageSerializer $serializer,
        private readonly SnsClient $client,
        private readonly string $destination,
    ) {
    }

    public function dispatch(
        Message ...$messages,
    ) : void {
        if (count($messages) === 0) {
            return;
        }

        if ($this->topicArn === null) {
            $this->topicArn = $this->getTopicArn($this->destination);
        }

        if (count($messages) === 1) {
            $payload = $this->serializeMessage($messages[0]);

            $payload['TopicArn'] = $this->topicArn;

            $this->client->publish($payload);

            return;
        }

        $messages = $this->serializeMessages($messages);

        $this->client->publishBatch([
            'PublishBatchRequestEntries' => $messages,
            'TopicArn' => $this->topicArn,
        ]);
    }

    /**
     * @return array{
     *    Message: mixed,
     *    MessageAttributes: array<string, array{DataType: string, StringValue: string}>
     *  }
     */
    private function serializeMessage(
        Message $message,
    ) : array {
        $message = $this->serializer->serializeMessage($message);

        return [
            'Message' => json_encode($message['payload']),
            'MessageAttributes' => $this->formatMessageAttributes($message['headers']),
            'MessageGroupId' => sprintf(
                '%s-%s',
                $message['headers']['__aggregate_root_type'],
                $message['headers']['__aggregate_root_id'],
            ),
        ];
    }

    /**
     * @param array<Message> $messages
     *
     * @return array<array{
     *    Message: mixed,
     *    MessageAttributes: array<string, array{DataType: string, StringValue: string}>
     *  }>
     */
    private function serializeMessages(
        array $messages,
    ) : array {
        return array_map(function ($message) : array {
            $id = $message->header('__event_id');

            $message = $this->serializeMessage($message);

            $message['Id'] = $id;

            return $message;
        }, $messages);
    }

    /**
     * @param array<string, string> $attributes
     *
     * @return array<string, array{DataType: string, StringValue: string}>
     */
    private function formatMessageAttributes(
        array $attributes,
    ) : array {
        return array_map(static function ($attribute) {
            return [
                'DataType' => 'String',
                'StringValue' => (string) $attribute,
            ];
        }, $attributes);
    }

    private function getTopicArn(
        string $topicName,
    ) : string {
        $result = $this->client->createTopic([
            'Name' => $topicName,
            'Attributes' => ['FifoTopic' => 'true'],
        ]);

        return $result['TopicArn'];
    }
}
