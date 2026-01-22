<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class RepositoryDispatcher
{
    public function __construct(
        public string $name,
    ) {
    }
}
