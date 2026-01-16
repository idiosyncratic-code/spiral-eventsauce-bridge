<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class AggregateRoot
{
    public function __construct(
        public string $table,
        public string|null $database = null,
    ) {
    }
}
