<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Console;

use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\AsyncMessageConsumer;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Attribute\Option;
use Spiral\Console\Command;
use Spiral\Core\FactoryInterface;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

use function in_array;
use function sprintf;

use const SIGHUP;
use const SIGINT;

#[AsCommand(name: 'eventsauce:consume', description: 'Consume and dispatch async messages')]
final class MessageConsumeCommand extends Command
{
    #[Argument]
    private string $consumerName;

    #[Option(name: 'run', mode: InputOption::VALUE_NEGATABLE)]
    private bool $loop = true;

    private bool $shouldRun = true;

    private AsyncMessageConsumer $consumer;

    public function perform(
        FactoryInterface $factory,
    ) : int {
        try {
            $this->consumer = $factory->make(
                AsyncMessageConsumer::class,
                context: $this->consumerName,
            );
        } catch (Throwable) {
            $this->error(sprintf('\'%s\' is not a valid consumer, exiting', $this->consumerName));

            return self::INVALID;
        }

        $this->info(sprintf('Starting Consumer \'%s\'', $this->consumerName));

        while ($this->shouldRun) {
            $this->consumer->consume($this->output, $this->loop);
        }

        return self::SUCCESS;
    }

    /** @return array<int, int> */
    public function getSubscribedSignals() : array
    {
        return [SIGINT, SIGHUP];
    }

    public function handleSignal(
        int $signal,
        int|false $previousExitCode = 0,
    ) : int|false {
        if (in_array($signal, $this->getSubscribedSignals(), true)) {
            $this->info(sprintf('Received Exit Signal %s', $signal));

            $this->shouldRun = false;

            $this->consumer->exit();
        }

        return $previousExitCode;
    }
}
