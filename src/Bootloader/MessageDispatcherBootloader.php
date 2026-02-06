<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\MessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\MessageDispatcherInjector;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class MessageDispatcherBootloader extends Bootloader
{
    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageDispatcher::class, MessageDispatcherInjector::class);
    }
}
