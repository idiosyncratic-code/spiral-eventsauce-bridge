<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws;

use Aws\Sqs\SqsClient;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;

use function array_map;
use function count;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class SqsMessageDispatcher implements MessageDispatcher
{
    private string|null $queueUrl = null;

    public function __construct(
        private readonly MessageSerializer $serializer,
        private readonly SqsClient $client,
        private readonly string $queueName,
    ) {
    }

    public function dispatch(
        Message ...$messages,
    ) : void {
        if (count($messages) === 0) {
            return;
        }

        if ($this->queueUrl === null) {
            $this->queueUrl = $this->getQueueUrl($this->queueName);
        }

        if (count($messages) === 1) {
            $payload = $this->serializeMessage($messages[0]);

            $payload['QueueUrl'] = $this->queueUrl;

            $this->client->sendMessage($payload);

            return;
        }

        $payload = ['Entries' => $this->serializeMessages($messages)];

        $payload['QueueUrl'] = $this->queueUrl;

        $this->client->sendMessageBatch($payload);
    }

    /**
     * @return array{
     *    MessageBody: non-empty-string,
     *    MessageAttributes: array<string, array{DataType: string, StringValue: string}>,
     *    MessageGroupId: string,
     *  }
     */
    private function serializeMessage(
        Message $message,
    ) : array {
        $message = $this->serializer->serializeMessage($message);

        return [
            'MessageBody' => json_encode($message['payload'], JSON_THROW_ON_ERROR),
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
     *    MessageBody: non-empty-string,
     *    MessageAttributes: array<string, array{DataType: string, StringValue: string}>,
     *    MessageGroupId: string,
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

    private function getQueueUrl(
        string $queueName,
    ) : string {
        $result = $this->client->getQueueUrl(['QueueName' => $queueName]);

        return $result['QueueUrl'];
    }
}
