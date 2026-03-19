<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\ExplicitlyMappedClassNameInflector;
use Idiosyncratic\Spiral\EventSauceBridge\AggregateRootRepositoryFactory;
use Idiosyncratic\Spiral\EventSauceBridge\ChainClassNameInflector;
use Idiosyncratic\Spiral\EventSauceBridge\Console\MessageConsumeCommand;
use Idiosyncratic\Spiral\EventSauceBridge\Console\OutboxRelayCommand;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\MessageDispatcherConfig;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Attribute\InitMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Console\Bootloader\ConsoleBootloader;

use function array_filter;
use function class_exists;
use function count;

final class EventSauceBootloader extends Bootloader
{
    #[InitMethod]
    public function registerCommands(
        ConsoleBootloader $console,
    ) : void {
        $console->addCommand(OutboxRelayCommand::class);
        $console->addCommand(MessageConsumeCommand::class);
    }

    #[BindMethod]
    public function createClassNameInflector(
        EventSauceConfig $config,
    ) : ClassNameInflector {
        $classMap = $config->inflectorClassMap();

        if (count($classMap) > 0) {
            return new ChainClassNameInflector(
                new ExplicitlyMappedClassNameInflector($classMap),
                new DotSeparatedSnakeCaseInflector(),
            );
        }

        return new DotSeparatedSnakeCaseInflector();
    }

    #[BootMethod]
    public function generateRepositoryClassesForDomains(
        EventSauceConfig $config,
        AggregateRootRepositoryFactory $repoFactory,
    ) : void {
        foreach ($config->domains() as $domainConfig) {
            $this->generateRepositoryClassesForDomain($config, $domainConfig, $repoFactory);
        }
    }

    /** @param array<string, mixed> $domainConfig */
    private function generateRepositoryClassesForDomain(
        EventSauceConfig $config,
        array $domainConfig,
        AggregateRootRepositoryFactory $repoFactory,
    ) : void {
        foreach ($domainConfig['aggregateRoots'] as $root => $rootConfig) {
            if (class_exists($rootConfig['repositoryClass'])) {
                continue;
            }

            $repoFactory->generateRepositoryClass(
                $rootConfig['namespace'],
                $rootConfig['repositoryClass'],
                $rootConfig['messageTable'],
                $domainConfig['database'],
                $root,
                $domainConfig['outbox']['enabled'],
                $domainConfig['outbox']['tableName'],
                $rootConfig['domain'],
                $this->getRepositoryDispatcherList(
                    $rootConfig['dispatchers'],
                    $config,
                    $domainConfig,
                ),
                $rootConfig['decorators'],
            );
        }
    }

    /**
     * @param array<mixed> $dispatchers
     * @param array<mixed> $domainConfig
     *
     * @return array<string>
     */
    private function getRepositoryDispatcherList(
        array $dispatchers,
        EventSauceConfig $config,
        array $domainConfig,
    ) : array {
        if ($domainConfig['outbox']['enabled'] === false) {
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
