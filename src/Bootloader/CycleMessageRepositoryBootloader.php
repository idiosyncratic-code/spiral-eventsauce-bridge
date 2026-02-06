<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use Cycle\Database\DatabaseProviderInterface;
use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Idiosyncratic\Spiral\EventSauceBridge\CycleMessageRepositoryFactory;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\MessageRepositoryInjector;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Attribute\SingletonMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class CycleMessageRepositoryBootloader extends Bootloader
{
    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageRepository::class, MessageRepositoryInjector::class);
    }

    #[BindMethod]
    public function createMessageSerializer(
        ClassNameInflector $classNameInflector,
    ) : MessageSerializer {
        return new ConstructingMessageSerializer(
            classNameInflector: $classNameInflector,
        );
    }

    #[SingletonMethod]
    public function createCycleMessageRepositoryFactory(
        DatabaseProviderInterface $dbProvider,
        MessageSerializer|null $serializer,
    ) : CycleMessageRepositoryFactory {
        return new CycleMessageRepositoryFactory(
            dbProvider: $dbProvider,
            serializer: $serializer,
        );
    }
}
