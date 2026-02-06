<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use Cycle\Database\DatabaseInterface;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\MessageOutbox\OutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\CycleOutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Stringable;

final class CycleOutboxRepositoryBootloader extends Bootloader implements InjectorInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly FactoryInterface $factory,
    ) {
    }

    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(OutboxRepository::class, self::class);
    }

    public function createInjection(
        ReflectionClass $class,
        Stringable|string|null $context = null,
    ) : OutboxRepository {
        $config = $this->container->get(EventSauceConfig::class);

        $database = $this->factory->make(
            DatabaseInterface::class,
            context: $config->outboxDatabase(),
        );

        return new CycleOutboxRepository(
            $database,
            $database->table($config->outboxTableName())->getName(),
            $this->container->get(MessageSerializer::class),
        );
    }
}
