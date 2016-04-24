<?php

use Utils\PhinxMigration;

class CreateGroupsTable extends PhinxMigration
{
    public function change()
    {
        $this
            ->table('groups')
            ->create();

        $this
            ->table('books')
            ->addColumn(
                'group_id',
                'integer',
                ['references' => 'groups', 'null' => true]
            )
            ->update();
    }
}
