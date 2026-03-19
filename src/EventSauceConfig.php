<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\MessageDispatcherConfig;
use Spiral\Core\InjectableConfig;

use function array_map;
use function array_merge;

final class EventSauceConfig extends InjectableConfig
{
    public const string CONFIG = 'eventsauce';

    private const array DEFAULT_DOMAIN_CONFIG = [
        'database' => 'default',
        'dispatchers' => [],
        'aggregateRoots' => [],
        'outbox' => [
            'enabled' => false,
            'tableName' => 'message_outbox',
            'batchSize' => 100,
            'commitSize' => 1,
        ],
    ];

    /**
     * @var array{
     *     inflectorClassMap: array<class-string, string|non-empty-array<string>>,
     *     domains: array<
     *         string, array{
     *             database: string,
     *             dispatchers: array<
     *                 string, array{
     *                     driver: string,
     *                     receiver?: bool,
     *                     consumers: array<class-string>,
     *                     aggregates: array<string>,
     *                 }
     *             >,
     *             aggregateRoots: array<class-string, mixed>,
     *             outbox: array{
     *                 enabled: bool,
     *                 tableName: string,
     *                 database: string|null,
     *                 batchSize: int,
     *                 commitSize: int,
     *             },
     *         }
     *     >,
     *     dispatchers: array<
     *         string, array{
     *             driver: string,
     *             receiver?: bool,
     *             consumers: array<class-string>,
     *             aggregates: array<string>,
     *         }
     *     >,
     *     drivers: array<string, AsyncMessageDispatcherConfig|MessageDispatcherConfig>,
     * }
     */
    protected array $config = [
        'inflectorClassMap' => [],
        'domains' => [],
        'dispatchers' => [],
        'drivers' => [],
        'aggregateRoots' => [],
    ];

    /** @return array<class-string, string|non-empty-array<string>> */
    public function inflectorClassMap() : array
    {
        return $this->config['inflectorClassMap'];
    }

    /** @return array<string, mixed> */
    public function domains() : array
    {
        $domainConfigs = $this->config['domains'];

        foreach ($domainConfigs as $domain => $config) {
            $domainConfigs[$domain] = $this->domain($domain);
        }

        return $domainConfigs;
    }

    /** @return array<mixed> */
    public function domain(
        string $domain,
    ) : array {
        return array_merge(
            self::DEFAULT_DOMAIN_CONFIG,
            $this->config['domains'][$domain],
        );
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
        $dispatcher = array_merge([
            'driver' => null,
            'receiver' => false,
            'consumers' => [],
            'aggregates' => [],
        ], $this->config['dispatchers'][$name]);

        $dispatcher['driver'] = $this->driver($dispatcher['driver']);

        return $dispatcher;
    }
}
