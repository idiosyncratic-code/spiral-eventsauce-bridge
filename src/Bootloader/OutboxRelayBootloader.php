<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Bootloader;

use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\MessageOutbox\OutboxRepository;
use EventSauce\MessageOutbox\RelayMessages;
use EventSauce\MessageOutbox\RelayMessagesThroughDispatcher;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageDispatcherConfig;
use Spiral\Boot\Attribute\BindMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\FactoryInterface;

use function count;

final class OutboxRelayBootloader extends Bootloader
{
    #[BindMethod]
    public function createOutboxRelay(
        EventSauceConfig $config,
        OutboxRepository|null $outboxRepository,
        MessageSerializer $serializer,
        FactoryInterface $factory,
    ) : RelayMessages {
        $dispatchers = [];
        foreach ($config->dispatchers() as $dispatcherName => $dispatcher) {
            if (! ($dispatcher['driver'] instanceof AsyncMessageDispatcherConfig)) {
                continue;
            }

            $dispatchers[] = $factory->make(
                MessageDispatcher::class,
                context: $dispatcherName,
            );
        }

        $dispatcher = count($dispatchers) > 1
            ? new MessageDispatcherChain(...$dispatchers)
            : $dispatchers[0];

        return new RelayMessagesThroughDispatcher($outboxRepository, $dispatcher);
    }
}
