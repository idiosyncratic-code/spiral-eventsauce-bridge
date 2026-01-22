<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class MessageDispatcher
{
    public function __construct(
        public string $name,
    ) {
    }
}
