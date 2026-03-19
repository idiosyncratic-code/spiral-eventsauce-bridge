<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\MessageOutbox\RelayMessages;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\OutboxRelayInjector;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class OutboxRelayBootloader extends Bootloader
{
    #[BootMethod]
    public function registerOutboxRelayInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(RelayMessages::class, OutboxRelayInjector::class);
    }
}
