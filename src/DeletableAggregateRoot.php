<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;

/**
 * @template-covariant AggregateRootIdType of AggregateRootId
 * @extends AggregateRoot<AggregateRootId>
 */
interface DeletableAggregateRoot extends AggregateRoot
{
    public function isDeleted() : bool;
}
