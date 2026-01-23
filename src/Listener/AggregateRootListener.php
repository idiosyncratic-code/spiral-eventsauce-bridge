<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Listener;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use Idiosyncratic\Spiral\EventSauceBridge\AggregateRootRepositoryFactory;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\AggregateRoot as AggregateRootAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDecorator;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\Bootloader\EventSauceConfigBootloader;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

use function sprintf;

#[TargetAttribute(AggregateRootAttribute::class)]
final class AggregateRootListener implements TokenizationListenerInterface
{
    private array $aggregateRoots = [];

    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly EventSauceConfigBootloader $config,
    ) {
    }

    /** @param ReflectionClass<AggregateRoot<AggregateRootId>> $class */
    public function listen(
        ReflectionClass $class,
    ) : void {
        $aggregateMetadata = $this->reader->firstClassMetadata($class, AggregateRootAttribute::class);

        if (! $aggregateMetadata instanceof AggregateRootAttribute) {
            return;
        }

        $namespace = $class->getNamespaceName();

        $idClass = $aggregateMetadata->idClass ??
            sprintf('%s\\%sId', $namespace, $class->getShortName());

        $repositoryClass = $aggregateMetadata->repoClass ??
            sprintf('%s\\%sRepository', $namespace, $class->getShortName());

        $config = [
            'namespace' => $namespace,
            'repositoryClass' => $repositoryClass,
            'idClass' => $idClass,
            'database' => $aggregateMetadata->database,
            'messageTable' => $aggregateMetadata->messageTable,
            'dispatchers' => [],
            'decorators' => [],
        ];

        $dispatcherMetadata = $this->reader->getClassMetadata($class, MessageDispatcher::class);

        $decoratorMetadata = $this->reader->getClassMetadata($class, MessageDecorator::class);

        foreach ($dispatcherMetadata as $dispatcher) {
            $config['dispatchers'][] = $dispatcher->name;
        }

        foreach ($decoratorMetadata as $decorator) {
            $config['decorators'][] = $decorator->name;
        }

        $this->aggregateRoots[$class->getName()] = $config;
    }

    public function finalize() : void
    {
        foreach ($this->aggregateRoots as $className => $config) {
            $this->config->registerAggregateRoot($className, $config);
        }
    }
}
