<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Console;

use EventSauce\MessageOutbox\RelayMessages;
use Idiosyncratic\Spiral\EventSauceBridge\EventSauceConfig;
use Spiral\Console\Attribute\Argument;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;
use Spiral\Core\FactoryInterface;

use function in_array;
use function sleep;
use function sprintf;

use const SIGHUP;
use const SIGINT;

#[AsCommand(name: 'eventsauce:relay', description: 'Run the Outbox Relay')]
final class OutboxRelayCommand extends Command
{
    #[Argument]
    private string $domain = 'default';

    private bool $shouldRun = true;

    public function __construct(
        private readonly EventSauceConfig $config,
    ) {
        parent::__construct();
    }

    public function perform(
        FactoryInterface $factory,
    ) : int {
        $this->info('Starting Outbox Relay');

        $domainConfig = $this->config->domain($this->domain);

        $relay = $factory->make(
            RelayMessages::class,
            context: $this->domain,
        );

        while ($this->shouldRun) {
            $numberOfMessagesDispatched = $relay->publishBatch(
                batchSize: $domainConfig['outbox']['batchSize'],
                commitSize: $domainConfig['outbox']['commitSize'],
            );

            if ($numberOfMessagesDispatched !== 0) {
                $this->info(
                    sprintf('Dispatched %d messages', $numberOfMessagesDispatched),
                );

                continue;
            }

            sleep(1);
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
            $this->shouldRun = false;
        }

        return $previousExitCode;
    }
}
