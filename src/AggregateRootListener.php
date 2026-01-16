<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Attribute\TargetAttribute;
use Spiral\Tokenizer\TokenizationListenerInterface;

use function sprintf;

#[TargetAttribute(AggregateRoot::class)]
final class AggregateRootListener implements TokenizationListenerInterface
{
    public function __construct(
        private ReaderInterface $reader,
        private LoggerInterface $log,
    ) {
    }

    public function listen(
        ReflectionClass $class,
    ) : void {
        $aggRoot = $this->reader->firstClassMetadata($class, AggregateRoot::class);

        $this->log->debug(sprintf(
            'Namespace: %s, Class: %s, Table: %s, DB: %s',
            $class->getNamespaceName(),
            $class->getShortName(),
            $aggRoot->table,
            $aggRoot->database,
        ));
    }

    public function finalize() : void
    {
    }
}
