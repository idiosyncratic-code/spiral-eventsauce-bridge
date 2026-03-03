<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\MessageDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\MessageConsumerInjector;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\MessageDispatcherInjector;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageConsumer;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class MessageDispatcherBootloader extends Bootloader
{
    #[BootMethod]
    public function registerMessageDispatcherInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageDispatcher::class, MessageDispatcherInjector::class);
    }

    #[BootMethod]
    public function registerMessageConsumerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(AsyncMessageConsumer::class, MessageConsumerInjector::class);
    }
}
