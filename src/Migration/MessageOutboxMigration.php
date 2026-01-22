<?php

declare(strict_types=1);

namespace Idiosyncratic\Spiral\EventSauceBridge\Migration;

use Cycle\Migrations\Migration;

abstract class MessageOutboxMigration extends Migration
{
    abstract protected function getTableName() : string;

    final public function up() : void
    {
        $this->table($this->getTableName())
            ->addColumn('id', 'primary', ['nullable' => false, 'default' => null])
            ->addColumn('consumed', 'boolean', ['nullable' => false, 'default' => false])
            ->addColumn('payload', 'json', ['nullable' => false])
            ->addIndex(
                ['consumed', 'id'],
            )
            ->create();
    }

    final public function down() : void
    {
        $this->table($this->getTableName())->drop();
    }
}
