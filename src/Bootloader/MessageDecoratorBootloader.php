<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\ClassNameInflector;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecorator;
use Idiosyncratic\Spiral\EventSauceBridge\Injector\MessageDecoratorInjector;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class MessageDecoratorBootloader extends Bootloader
{
    #[BootMethod]
    public function registerInjector(
        BinderInterface $binder,
    ) : void {
        $binder->bindInjector(MessageDecorator::class, MessageDecoratorInjector::class);
    }

    #[BindMethod]
    public function createMessageDefaultHeadersDecorator(
        ClassNameInflector|null $inflector,
    ) : DefaultHeadersDecorator {
        return new DefaultHeadersDecorator(
            inflector: $inflector,
        );
    }
}
