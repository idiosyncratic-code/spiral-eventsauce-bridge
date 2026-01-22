<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Listener;

use EventSauce\EventSourcing\AggregateRootId;
use Idiosyncratic\Spiral\EventSauceBridge\Attribute\AggregateRootId as AggregateRootIdAttribute;
use Idiosyncratic\Spiral\EventSauceBridge\Bootloader\EventSauceConfigBootloader;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

#[TargetAttribute(AggregateRootIdAttribute::class)]
final class AggregateRootIdListener implements TokenizationListenerInterface
{
    /** @var array<class-string, string> **/
    private array $classMap = [];

    public function __construct(
        private ReaderInterface $reader,
        private EventSauceConfigBootloader $config,
    ) {
    }

    /** @param ReflectionClass<AggregateRootId> $class> */
    public function listen(
        ReflectionClass $class,
    ) : void {
        $attribute = $this->reader->firstClassMetadata($class, AggregateRootIdAttribute::class);

        if (! $attribute instanceof AggregateRootIdAttribute) {
            return;
        }

        $this->classMap[$class->getName()] = $attribute->name;
    }

    public function finalize() : void
    {
        foreach ($this->classMap as $className => $eventName) {
            $this->config->mapIdClass($className, $eventName);
        }
    }
}
