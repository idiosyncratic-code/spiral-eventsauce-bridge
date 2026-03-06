<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\MessageDispatcherConfig;
use Spiral\Core\InjectableConfig;

use function array_map;

final class EventSauceConfig extends InjectableConfig
{
    public const string CONFIG = 'eventsauce';

    /**
     * @var array{
     *     inflectorClassMap: array<class-string, string|non-empty-array<string>>,
     *     dispatchers: array<
     *         string, array{
     *             driver: string,
     *             receiver?: bool,
     *             consumers: array<class-string>,
     *             aggregates: array<string>,
     *         }
     *     >,
     *     drivers: array<string, AsyncMessageDispatcherConfig|MessageDispatcherConfig>,
     *     aggregateRoots: array<class-string, mixed>,
     *     outbox: array{
     *         enabled: bool,
     *         tableName: string,
     *         database: string|null,
     *         batchSize: int,
     *         commitSize: int,
     *     },
     * }
     */
    protected array $config = [
        'inflectorClassMap' => [],
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

    /** @return array<class-string, string|non-empty-array<string>> */
    public function inflectorClassMap() : array
    {
        return $this->config['inflectorClassMap'];
    }

    /** @return array<class-string, mixed> */
    public function aggregateRoots() : array
    {
        return $this->config['aggregateRoots'];
    }

    /** @return array<string, mixed> */
    public function dispatchers() : array
    {
        return array_map(function ($dispatcher) {
            $dispatcher['driver'] = $this->driver($dispatcher['driver']);

            return $dispatcher;
        }, $this->config['dispatchers']);
    }

    /**
     * @return array{
     *      driver: AsyncMessageDispatcherConfig|MessageDispatcherConfig,
     *      receiver?: bool,
     *      consumers: array<class-string>,
     *      aggregates: array<string>,
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
