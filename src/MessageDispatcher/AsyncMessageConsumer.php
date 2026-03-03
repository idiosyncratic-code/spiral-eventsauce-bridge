<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher;

use Symfony\Component\Console\Style\SymfonyStyle;

interface AsyncMessageConsumer
{
    public function consume(
        SymfonyStyle $output,
        bool $continue = true,
    ) : bool;

    public function exit() : void;
}
