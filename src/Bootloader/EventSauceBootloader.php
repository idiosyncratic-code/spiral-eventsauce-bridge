<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\ExplicitlyMappedClassNameInflector;
use EventSauce\MessageOutbox\RelayMessages;
use Idiosyncratic\Spiral\EventSauceBridge\AggregateRootRepositoryFactory;
use Idiosyncratic\Spiral\EventSauceBridge\ChainClassNameInflector;
use Idiosyncratic\Spiral\EventSauceBridge\Console\OutboxRelayCommand;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\MessageDispatcherConfig;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Attribute\InitMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Console\Bootloader\ConsoleBootloader;

use function array_filter;
use function array_merge;
use function class_exists;

final class EventSauceBootloader extends Bootloader
{
    #[InitMethod]
    public function registerCommands(
        ConsoleBootloader $console,
    ) : void {
        $console->addCommand(OutboxRelayCommand::class);
    }

    #[BindMethod]
    public function createClassNameInflector(
        EventSauceConfig $config,
    ) : ClassNameInflector {
        return new ChainClassNameInflector(
            new ExplicitlyMappedClassNameInflector(
                array_merge($config->eventClassMap(), $config->idClassMap()),
            ),
            new DotSeparatedSnakeCaseInflector(),
        );
    }

    #[BindMethod]
    public function createRelayMessages(
        EventSauceConfig $config,
    ) : RelayMessages {
        $dispatchers = [];

        foreach ($config['dispatchers'] as $dispatcher) {
            if (! $config->dispatcher($dispatcher)['driver'] instanceof AsyncMessageDispatcherConfig) {
                continue;
            }

            $dispatchers[] = $dispatcher;
        }
    }

    #[BootMethod]
    public function generateRepositoryClasses(
        EventSauceConfig $config,
        AggregateRootRepositoryFactory $repoFactory,
    ) : void {
        foreach ($config->aggregateRoots() as $root => $rootConfig) {
            if (class_exists($rootConfig['repositoryClass'])) {
                continue;
            }

            $repoFactory->generateRepositoryClass(
                $rootConfig['namespace'],
                $rootConfig['repositoryClass'],
                $rootConfig['messageTable'],
                $rootConfig['database'],
                $root,
                $config->outboxEnabled(),
                $config->outboxTableName(),
                $this->getRepositoryDispatcherList($rootConfig['dispatchers'], $config),
                $rootConfig['decorators'],
            );
        }
    }

    /**
     * @param array<string> $dispatchers
     *
     * @return array<string>
     */
    private function getRepositoryDispatcherList(
        array $dispatchers,
        EventSauceConfig $config,
    ) : array {
        if ($config->outboxEnabled() === false) {
            return $dispatchers;
        }

        return array_filter(
            $dispatchers,
            static function ($dispatcher) use ($config) {
                return $config->dispatcher($dispatcher)['driver'] instanceof MessageDispatcherConfig;
            },
        );
    }
}
