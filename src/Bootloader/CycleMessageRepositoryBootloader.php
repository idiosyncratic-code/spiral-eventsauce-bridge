<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use Cycle\Database\DatabaseProviderInterface;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\MessageRepositoryTable;
use Idiosyncratic\Spiral\EventSauceBridge\CycleMessageRepositoryFactory;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Attribute\SingletonMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\InjectorInterface;
use Stringable;

final class CycleMessageRepositoryBootloader extends Bootloader implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageRepository::class, self::class);
    }

    #[SingletonMethod]
    public function createCycleMessageRepositoryFactory(
        ClassNameInflector $classNameInflector,
        DatabaseProviderInterface $dbProvider,
    ) : CycleMessageRepositoryFactory {
        $serializer = new ConstructingMessageSerializer(
            classNameInflector: $this->container->get(ClassNameInflector::class),
        );

        return new CycleMessageRepositoryFactory(
            dbProvider: $dbProvider,
            serializer: $serializer,
        );
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : MessageRepository {
        if (! $context instanceof ReflectionParameter) {
            throw new RuntimeException('not a reflection parameter');
        }

        $tableAttribute = $context->getAttributes(MessageRepositoryTable::class)[0]?->newInstance();

        if (! $tableAttribute instanceof MessageRepositoryTable) {
            throw new RuntimeException('no message repository table attr');
        }

        $database = $tableAttribute->database;

        $table = $tableAttribute->table;

        $factory = $this->container->get(CycleMessageRepositoryFactory::class);

        return $factory->makeMessageRepository(
            $tableAttribute->database,
            $tableAttribute->table,
            $tableAttribute->useOutbox,
            $tableAttribute->outboxTableName,
        );
    }
}
