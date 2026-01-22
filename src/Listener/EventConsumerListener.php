<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Listener;

use EventSauce\EventSourcing\EventConsumption\EventConsumer;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\EventConsumer as EventConsumerAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\Bootloader\EventSauceConfigBootloader;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

use function array_unique;

#[TargetAttribute(EventConsumerAttribute::class)]
final class EventConsumerListener implements TokenizationListenerInterface
{
    /** @var array<string, array<class-string>> **/
    private array $dispatchers = [];

    public function __construct(
        private ReaderInterface $reader,
        private EventSauceConfigBootloader $config,
    ) {
    }

    /** @param ReflectionClass<EventConsumer> $class */
    public function listen(
        ReflectionClass $class,
    ) : void {
        $attributes = $this->reader->getClassMetadata($class, EventConsumerAttribute::class);

        foreach ($attributes as $attribute) {
            if (isset($this->dispatchers[$attribute->dispatcher]) === false) {
                $this->dispatchers[$attribute->dispatcher] = [];
            }

            $this->dispatchers[$attribute->dispatcher][] = $class->getName();
        }
    }

    public function finalize() : void
    {
        foreach ($this->dispatchers as $dispatcher => $listeners) {
            $listeners = array_unique($listeners);
            foreach ($listeners as $listener) {
                $this->config->registerConsumer($dispatcher, $listener);
            }
        }
    }
}
