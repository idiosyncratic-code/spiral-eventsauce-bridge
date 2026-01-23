<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\ExplicitlyMappedClassNameInflector;
use EventSauce\EventSourcing\MessageDecorator;
use Idiosyncratic\Spiral\EventSauceBridge\AggregateRootRepositoryFactory;
use Idiosyncratic\Spiral\EventSauceBridge\ChainClassNameInflector;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;

use function array_merge;

final class EventSauceBootloader extends Bootloader
{
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

    #[BootMethod]
    public function generateRepositoryClasses(
        EventSauceConfig $config,
        AggregateRootRepositoryFactory $repoFactory,
    ) : void {
        foreach ($config->aggregateRoots() as $root => $rootConfig) {
            if (class_exists($rootConfig['repositoryClass'])) {
                continue;
            }

            if ($config->useOutbox() === true) {
                $dispatchers = [];
                foreach ($rootConfig['dispatchers'] as $dispatcher) {
                    if (is_subclass_of($config->dispatcher($dispatcher)['config'], AsyncMessageDispatcherConfig::class) === false) {
                        $dispatchers[] = $dispatcher;
                    }
                }
            }

            $repoFactory->generateRepositoryClass(
                $rootConfig['namespace'],
                $rootConfig['repositoryClass'],
                $rootConfig['messageTable'],
                $rootConfig['database'],
                $root,
                $config->useOutbox(),
                $config->outboxTableName(),
                $dispatchers,
                $rootConfig['decorators'],
            );
        }
    }
}
