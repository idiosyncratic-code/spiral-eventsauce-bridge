<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Listener;

use Idiosyncratic\Spiral\EventSauceBridge\Attribute\DomainEventName;
use Idiosyncratic\Spiral\EventSauceBridge\Bootloader\EventSauceConfigBootloader;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(DomainEventName::class)]
final class DomainEventNameListener implements TokenizationListenerInterface
{
    /** @var array<class-string, array<string>> **/
    private array $classMap = [];

    public function __construct(
        private ReaderInterface $reader,
        private EventSauceConfigBootloader $config,
    ) {
    }

    /** @param ReflectionClass<object> $class */
    public function listen(
        ReflectionClass $class,
    ) : void {
        $attributes = $this->reader->getClassMetadata($class, DomainEventName::class);

        $eventNames = [];

        foreach ($attributes as $attribute) {
            $eventNames[] = $attribute->name;
        }

        $this->classMap[$class->getName()] = $eventNames;
    }

    public function finalize() : void
    {
        foreach ($this->classMap as $className => $eventNames) {
            $this->config->mapEventClass($className, ...$eventNames);
        }
    }
}
