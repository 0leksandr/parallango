<?php

use Utils\PhinxMigration;

class CreateLoadablesTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('loadables')
            ->addColumn('entity', 'integer', ['references' => 'entity_types'])
            ->addColumn(
                'related_entity',
                'integer',
                [
                    'references' => 'entity_types',
                    'null' => true,
                ]
            )
            ->addColumn('related_id', 'integer', ['null' => true])
            ->addColumn('offset', 'integer', ['default' => 0])
            ->create();
    }
}
