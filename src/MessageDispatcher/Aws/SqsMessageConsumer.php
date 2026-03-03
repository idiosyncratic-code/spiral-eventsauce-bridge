<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\Aws;

use Aws\Sqs\SqsClient;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageConsumer;
use RuntimeException;
use Spiral\Console\Traits\HelpersTrait;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_map;
use function count;
use function json_decode;
use function sprintf;

final class SqsMessageConsumer implements AsyncMessageConsumer
{
    use HelpersTrait;

    private string|null $queueUrl = null;

    private bool $shouldRun = true;

    public function __construct(
        private readonly SqsClient $client,
        private readonly MessageSerializer $serializer,
        private readonly string $queueName,
        private readonly SynchronousMessageDispatcher $dispatcher,
    ) {
    }

    public function consume(
        SymfonyStyle $output,
        bool $continue = true,
    ) : bool {
        $this->output = $output;

        if ($this->queueUrl === null) {
            $this->queueUrl = $this->getQueueUrl($this->queueName);
        }

        while ($this->shouldRun === true) {
            $this->shouldRun = $continue;

            $this->info('Retrieving Messages...');

            $messages = $this->receiveMessages();

            $receivedMessageCount = count($messages);

            $this->info(
                sprintf('Received %d messages', $receivedMessageCount),
            );

            foreach ($messages as $message) {
                $unserializedMessage = $this->unserializeMessage($message);

                $this->dispatcher->dispatch($unserializedMessage);

                $this->acknowledgeMessage($message);
            }
        }

        return true;
    }

    public function exit() : void
    {
        $this->shouldRun = false;
    }

    /** @return array<mixed> */
    private function receiveMessages() : array
    {
        $arguments = [
            'AttributeNames' => ['All'],
            'MessageAttributeNames' => ['All'],
            'MaxNumberOfMessages' => 10,
            'QueueUrl' => $this->queueUrl,
            'WaitTimeSeconds' => 20,
            'VisibilityTimeout' => 30,
        ];

        $result = $this->client->receiveMessage($arguments);

        if ($result->hasKey('Messages')) {
            return $result->get('Messages');
        }

        return [];
    }

    private function getQueueUrl(
        string $queueName,
    ) : string {
        $result = $this->client->getQueueUrl(['QueueName' => $queueName]);

        return $result['QueueUrl'];
    }

    /** @param array<mixed> $message */
    private function acknowledgeMessage(
        array $message,
    ) : void {
        $this->info(sprintf('Acknowledging Message %s', $message['ReceiptHandle']));

        $this->client->deleteMessage([
            'QueueUrl' => $this->queueUrl,
            'ReceiptHandle' => $message['ReceiptHandle'],
        ]);
    }

    /** @param array<mixed> $message */
    private function unserializeMessage(
        array $message,
    ) : Message {
        if (! isset($message['Body'])) {
            throw new RuntimeException('Could not unserialize message');
        }

        $data = json_decode($message['Body'], true);

        if (isset($data['TopicArn']) === true) {
            $payload = $this->getPayloadFromSnsMessage($message['Body']);
        } else {
            $payload = $this->getPayloadFromSqsMessage($message);
        }

        return $this->serializer->unserializePayload($payload);
    }

    /**
     * @return array{
     *   payload: mixed,
     *   headers: array<string, string>
     * }
     */
    private function getPayloadFromSnsMessage(
        string $messageBody,
    ) : array {
        $data = json_decode($messageBody, true);

        $headers = array_map(static function ($header) {
            return $header['Value'];
        }, $data['MessageAttributes']);

        $headers['__message_dispatcher'] = 'sns';

        $payload = json_decode($data['Message'], true);

        return ['payload' => $payload, 'headers' => $headers];
    }

    /**
     * @param array<mixed> $data
     *
     * @return array{
     *   payload: mixed,
     *   headers: array<string, string>
     * }
     */
    private function getPayloadFromSqsMessage(
        array $data,
    ) : array {
        $headers = array_map(static function ($header) {
            return $header['StringValue'];
        }, $data['MessageAttributes']);

        $headers['__message_dispatcher'] = 'sqs';

        $payload = json_decode($data['Body'], true);

        return ['payload' => $payload, 'headers' => $headers];
    }
}
