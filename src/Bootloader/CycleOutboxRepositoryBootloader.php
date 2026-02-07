<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\MessageOutbox\OutboxRepository;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\OutboxRepositoryInjector;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class CycleOutboxRepositoryBootloader extends Bootloader
{
    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(OutboxRepository::class, OutboxRepositoryInjector::class);
    }
}
