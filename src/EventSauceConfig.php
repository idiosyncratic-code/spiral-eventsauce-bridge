<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\MessageDispatcherConfig;
use Spiral\Core\InjectableConfig;

final class EventSauceConfig extends InjectableConfig
{
    public const string CONFIG = 'eventsauce';

    /**
     * @var array{
     *     'eventClassMap': array<class-string, string|array<string>>,
     *     'idClassMap': array<class-string, string>,
     *     'dispatchers': array<
     *         string, array{
     *             'driver': string,
     *             'consumers': array<class-string>,
     *         }
     *     >,
     *     'drivers': array<string, AsyncMessageDispatcherConfig|MessageDispatcherConfig>,
     *     'aggregateRoots': array<string, mixed>,
     *     'outbox': array{
     *         'enabled': bool,
     *         'tableName': string,
     *         'database': string|null,
     *         'batchSize': int,
     *         'commitSize': int,
     *     },
     * }
     */
    protected array $config = [
        'eventClassMap' => [],
        'idClassMap' => [],
        'dispatchers' => [],
        'drivers' => [],
        'aggregateRoots' => [],
        'outbox' => [
            'enabled' => false,
            'tableName' => 'message_outbox',
            'database' => null,
            'batchSize' => 1,
            'commitSize' => 1,
        ],
    ];

    /** @return array<class-string, string|array<string>> */
    public function eventClassMap() : array
    {
        return $this->config['eventClassMap'];
    }

    /** @return array<class-string, string> */
    public function idClassMap() : array
    {
        return $this->config['idClassMap'];
    }

    /** @return array<string, mixed> */
    public function aggregateRoots() : array
    {
        return $this->config['aggregateRoots'];
    }

    /** @return array<string, mixed> */
    public function dispatchers() : array
    {
        $resolvedDispatchers = [];

        foreach ($this->config['dispatchers'] as $dispatcherName => $dispatcher) {
            $dispatcher['driver'] = $this->driver($dispatcher['driver']);

            $resolvedDispatchers[$dispatcherName] = $dispatcher;
        }

        return $resolvedDispatchers;
    }

    /**
     * @return array{
     *      driver: AsyncMessageDispatcherConfig|MessageDispatcherConfig,
     *      consumers: array<class-string>,
     *  }
     */
    public function dispatcher(
        string $name,
    ) : array {
        $dispatcher = $this->config['dispatchers'][$name];

        $dispatcher['driver'] = $this->driver($dispatcher['driver']);

        return $dispatcher;
    }

    /** @return array<string, AsyncMessageDispatcherConfig|MessageDispatcherConfig> */
    public function drivers() : array
    {
        return $this->config['drivers'];
    }

    public function driver(
        string $name,
    ) : MessageDispatcherConfig|AsyncMessageDispatcherConfig {
        return $this->config['drivers'][$name];
    }

    public function outboxEnabled() : bool
    {
        return $this->config['outbox']['enabled'];
    }

    public function outboxTableName() : string
    {
        return $this->config['outbox']['tableName'];
    }

    public function outboxDatabase() : string
    {
        return $this->config['outbox']['database'];
    }

    public function outboxBatchSize() : int
    {
        return $this->config['outbox']['batchSize'];
    }

    public function outboxCommitSize() : int
    {
        return $this->config['outbox']['commitSize'];
    }
}
