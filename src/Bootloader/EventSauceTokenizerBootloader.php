<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use Idiosyncratic\Spiral\EventSauceBridge\Listener\AggregateRootIdListener;
use Idiosyncratic\Spiral\EventSauceBridge\Listener\AggregateRootListener;
use Idiosyncratic\Spiral\EventSauceBridge\Listener\DomainEventNameListener;
use Idiosyncratic\Spiral\EventSauceBridge\Listener\EventConsumerListener;
use Spiral\Boot\Attribute\InitMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

final class EventSauceTokenizerBootloader extends Bootloader
{
    #[InitMethod]
    public function addAggregateRootListener(
        TokenizerListenerRegistryInterface $listenerRegistry,
        AggregateRootListener $listener,
    ) : void {
        $listenerRegistry->addListener($listener);
    }

    #[InitMethod]
    public function addDomainEventNameListener(
        TokenizerListenerRegistryInterface $listenerRegistry,
        DomainEventNameListener $listener,
    ) : void {
        $listenerRegistry->addListener($listener);
    }

    #[InitMethod]
    public function addAggregateRootIdListener(
        TokenizerListenerRegistryInterface $listenerRegistry,
        AggregateRootIdListener $listener,
    ) : void {
        $listenerRegistry->addListener($listener);
    }

    #[InitMethod]
    public function addEventConsumerListener(
        TokenizerListenerRegistryInterface $listenerRegistry,
        EventConsumerListener $listener,
    ) : void {
        $listenerRegistry->addListener($listener);
    }
}
