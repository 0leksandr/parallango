<?php

use Utils\PhinxMigration;

class CreateEntityTypesTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('entity_types')
            ->addColumn(
                'entity_type',
                'string',
                [
                    'length' => 16,
                    'unique' => true,
                ]
            )
            ->create();
    }
}
