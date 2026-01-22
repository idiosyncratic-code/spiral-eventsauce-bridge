<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Migration;

use Cycle\Migrations\Migration;

use function sprintf;

abstract class EventStoreMigration extends Migration
{
    /** @return array<string> */
    abstract protected function getTableNames() : array;

    final public function up() : void
    {
        foreach ($this->getTableNames() as $tableName) {
            $this->table(sprintf('%s_event_store', $tableName))
                ->addColumn('id', 'primary', ['nullable' => false, 'default' => null])
                ->addColumn('event_id', 'uuid', ['nullable' => false])
                ->addColumn('aggregate_root_id', 'uuid', ['nullable' => false])
                ->addColumn('version', 'bigInteger', ['nullable' => false, 'default' => null])
                ->addColumn('payload', 'json', ['nullable' => false])
                ->addIndex(
                    ['aggregate_root_id', 'version'],
                    ['name' => sprintf('%s_reconstitution', $tableName), 'unique' => true],
                )
                ->create();
        }
    }

    final public function down() : void
    {
        foreach ($this->getTableNames() as $tableName) {
            $this->table(sprintf('%s_event_store', $tableName))->drop();
        }
    }
}
