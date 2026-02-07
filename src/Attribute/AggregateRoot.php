<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class AggregateRoot
{
    public function __construct(
        public readonly string $messageTable,
        public readonly string $database = 'default',
        public readonly string|null $name = null,
        public readonly string|null $idClass = null,
        public readonly string|null $repoClass = null,
    ) {
    }
}
