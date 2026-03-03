<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\SyncMessageDispatcherConfig;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Attribute\Singleton;

use function sprintf;

#[Singleton]
final class EventSauceConfigBootloader extends Bootloader
{
    /** @param ConfiguratorInterface<object> $configurator */
    public function __construct(
        private readonly ConfiguratorInterface $configurator,
    ) {
    }

    public function init() : void
    {
        $this->configurator->setDefaults(EventSauceConfig::CONFIG, [
            'idClassMap' => [],
            'dispatchers' => [
                'sync' => [
                    'driver' => 'sync',
                    'receiver' => false,
                    'consumers' => [],
                ],
            ],
            'drivers' => [
                'sync' => new SyncMessageDispatcherConfig(),
            ],
            'aggregateRoots' => [],
            'outbox' => [
                'enabled' => false,
                'tableName' => 'message_outbox',
                'database' => null,
                'batchSize' => 1,
                'commitSize' => 1,
            ],
        ]);
    }

    public function mapClassInflector(
        string $className,
        string ...$inflectedClassNames,
    ) : void {
        if (count($inflectedClassNames) === 1) {
            $this->configurator->modify(
                EventSauceConfig::CONFIG,
                new Append('inflectorClassMap', $className, $inflectedClassNames[0]),
            );

            return;
        }

        $this->configurator->modify(
            EventSauceConfig::CONFIG,
            new Append('inflectorClassMap', $className, $inflectedClassNames),
        );
    }

    /** @param class-string $consumerClassName */
    public function registerConsumer(
        string $dispatcherName,
        string $consumerClassName,
    ) : void {
        $this->configurator->modify(
            EventSauceConfig::CONFIG,
            new Append(
                position: sprintf('dispatchers.%s.consumers', $dispatcherName),
                key: null,
                value: $consumerClassName,
            ),
        );
    }

    /**
     * @param class-string $className
     * @param array<string, mixed> $config
     */
    public function registerAggregateRoot(
        string $className,
        array $config,
    ) : void {
        $this->configurator->modify(
            EventSauceConfig::CONFIG,
            new Append(
                position: 'aggregateRoots',
                key: $className,
                value: $config,
            ),
        );
    }
}
