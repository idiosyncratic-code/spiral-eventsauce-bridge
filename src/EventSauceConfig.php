<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge;

use Idiosyncratic\Spiral\EventSauceBridge\MessageDispatcher\MessageDispatcherConfig;
use Spiral\Core\InjectableConfig;

final class EventSauceConfig extends InjectableConfig
{
    public const string CONFIG = 'eventsauce';

    /** @var array{
     *     'eventClassMap': array<class-string, string|array<string>>,
     *     'idClassMap': array<class-string, string>,
     *     'dispatchers': array{string, array{
     *         config: MessageDispatcherConfig,
     *         consumers: array<class-string>,
     *     }}
     * }
     */
    protected array $config = [
        'eventClassMap' => [],
        'idClassMap' => [],
        'dispatchers' => [],
        'aggregateRoots' => [],
        'useOutbox' => false,
        'outboxTableName' => 'message_outbox',
    ];

    /** @return array<class-string, string|array<string>> */
    public function eventClassMap() : array
    {
        return $this->config['eventClassMap'];
    }

    /** @return array<class-string, string> */
    public function idClassMap() : array
    {
        return $this->config['idClassMap'];
    }

    /** @return array{class-string, array{
     *      config: MessageDispatcherConfig,
     *      consumers: array<class-string>,
     *  }}
     */
    public function aggregateRoots() : array
    {
        return $this->config['aggregateRoots'];
    }

    /** @return array{string, array{
     *      config: MessageDispatcherConfig,
     *      consumers: array<class-string>,
     *  }}
     */
    public function dispatchers() : array
    {
        return $this->config['dispatchers'];
    }

    /** @return array{
     *      config: MessageDispatcherConfig,
     *      consumers: array<class-string>,
     *  }
     */
    public function dispatcher(
        string $name,
    ) : array {
        return $this->config['dispatchers'][$name];
    }

    public function useOutbox() : bool
    {
        return $this->config['useOutbox'];
    }

    public function outboxTableName() : string
    {
        return $this->config['outboxTableName'];
    }

}
